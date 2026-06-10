<?php

declare(strict_types=1);

namespace Nexus\Repository;

use PDO;
use Nexus\Model\Jogador;

/**
 * PASSO 2 — Repositório
 * TODO o SQL do sistema fica aqui. Service e Controller não tocam em PDO.
 */
class JogadorRepository implements IJogadorRepository
{
    public function __construct(private readonly PDO $pdo) {}

    // ─────────────────────────────────────────────
    // SAVE (INSERT ou UPDATE)
    // ─────────────────────────────────────────────
    public function save(Jogador $jogador): int
    {
        if ($jogador->id === null) {
            return $this->insert($jogador);
        }
        $this->update($jogador);
        return $jogador->id;
    }

    private function insert(Jogador $jogador): int
    {
        $sql = '
            INSERT INTO jogadores
                (nome, genero, sync, suspeita, frags, cena_atual,
                 path_escolhido, flags, inventario, lore,
                 finais_desbloqueados, mortes_desbloqueadas)
            VALUES
                (:nome, :genero, :sync, :suspeita, :frags, :cena_atual,
                 :path_escolhido, :flags, :inventario, :lore,
                 :finais, :mortes)
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bind($jogador));
        return (int) $this->pdo->lastInsertId();
    }

    private function update(Jogador $jogador): void
    {
        $sql = '
            UPDATE jogadores SET
                nome                 = :nome,
                genero               = :genero,
                sync                 = :sync,
                suspeita             = :suspeita,
                frags                = :frags,
                cena_atual           = :cena_atual,
                path_escolhido       = :path_escolhido,
                flags                = :flags,
                inventario           = :inventario,
                lore                 = :lore,
                finais_desbloqueados = :finais,
                mortes_desbloqueadas = :mortes,
                atualizado_em        = NOW()
            WHERE id = :id
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([...$this->bind($jogador), ':id' => $jogador->id]);
    }

    private function bind(Jogador $j): array
    {
        return [
            ':nome'           => $j->nome,
            ':genero'         => $j->genero,
            ':sync'           => $j->sync,
            ':suspeita'       => $j->suspeita,
            ':frags'          => $j->frags,
            ':cena_atual'     => $j->cena_atual,
            ':path_escolhido' => $j->path_escolhido,
            ':flags'          => $j->flagsJson(),
            ':inventario'     => $j->inventarioJson(),
            ':lore'           => $j->loreJson(),
            ':finais'         => $j->finaisJson(),
            ':mortes'         => $j->mortesJson(),
        ];
    }

    // ─────────────────────────────────────────────
    // FIND
    // ─────────────────────────────────────────────
    public function find(int $id): ?Jogador
    {
        $stmt = $this->pdo->prepare('SELECT * FROM jogadores WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findByNome(string $nome): ?Jogador
    {
        $stmt = $this->pdo->prepare('SELECT * FROM jogadores WHERE nome = :nome LIMIT 1');
        $stmt->execute([':nome' => $nome]);
        $row = $stmt->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM jogadores ORDER BY atualizado_em DESC');
        return array_map([$this, 'hydrate'], $stmt->fetchAll());
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM jogadores WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ─────────────────────────────────────────────
    // UPDATE PROGRESSO (rota de autosave)
    // ─────────────────────────────────────────────
    public function updateProgresso(Jogador $jogador): bool
    {
        $sql = '
            UPDATE jogadores SET
                sync           = :sync,
                suspeita       = :suspeita,
                frags          = :frags,
                cena_atual     = :cena_atual,
                path_escolhido = :path_escolhido,
                flags          = :flags,
                inventario     = :inventario,
                lore           = :lore,
                atualizado_em  = NOW()
            WHERE id = :id
        ';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sync'           => $jogador->sync,
            ':suspeita'       => $jogador->suspeita,
            ':frags'          => $jogador->frags,
            ':cena_atual'     => $jogador->cena_atual,
            ':path_escolhido' => $jogador->path_escolhido,
            ':flags'          => $jogador->flagsJson(),
            ':inventario'     => $jogador->inventarioJson(),
            ':lore'           => $jogador->loreJson(),
            ':id'             => $jogador->id,
        ]);
        return $stmt->rowCount() > 0;
    }

    // ─────────────────────────────────────────────
    // ADICIONAR FINAL / MORTE AO MURAL
    // ─────────────────────────────────────────────
    public function adicionarFinal(int $id, string $chave, string $tipo): bool
    {
        $jogador = $this->find($id);
        if (!$jogador) return false;

        if ($tipo === 'ending') {
            if (!in_array($chave, $jogador->finais_desbloqueados, true)) {
                $jogador->finais_desbloqueados[] = $chave;
            }
        } else {
            if (!in_array($chave, $jogador->mortes_desbloqueadas, true)) {
                $jogador->mortes_desbloqueadas[] = $chave;
            }
        }

        $col = $tipo === 'ending' ? 'finais_desbloqueados' : 'mortes_desbloqueadas';
        $val = $tipo === 'ending' ? $jogador->finaisJson() : $jogador->mortesJson();

        $stmt = $this->pdo->prepare(
            "UPDATE jogadores SET {$col} = :val, atualizado_em = NOW() WHERE id = :id"
        );
        $stmt->execute([':val' => $val, ':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // ─────────────────────────────────────────────
    // HYDRATE — row array → Jogador
    // ─────────────────────────────────────────────
    private function hydrate(array $row): Jogador
    {
        return new Jogador(
            id:                    (int) $row['id'],
            nome:                  $row['nome'],
            genero:                $row['genero'],
            sync:                  (int) $row['sync'],
            suspeita:              (int) $row['suspeita'],
            frags:                 (int) $row['frags'],
            cena_atual:            $row['cena_atual'],
            path_escolhido:        $row['path_escolhido'] ?? '',
            flags:                 json_decode($row['flags'] ?? '{}', true) ?: [],
            inventario:            json_decode($row['inventario'] ?? '[]', true) ?: [],
            lore:                  json_decode($row['lore'] ?? '[]', true) ?: [],
            finais_desbloqueados:  json_decode($row['finais_desbloqueados'] ?? '[]', true) ?: [],
            mortes_desbloqueadas:  json_decode($row['mortes_desbloqueadas'] ?? '[]', true) ?: [],
            criado_em:             $row['criado_em'] ?? null,
            atualizado_em:         $row['atualizado_em'] ?? null,
        );
    }
}
