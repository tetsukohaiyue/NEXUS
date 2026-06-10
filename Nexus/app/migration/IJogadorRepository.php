<?php

declare(strict_types=1);

namespace Nexus\Repository;

use Nexus\Model\Jogador;

/**
 * PASSO 2 — Interface / Contrato do Repositório
 * Define o que qualquer repositório de Jogador DEVE implementar.
 */
interface IJogadorRepository
{
    /** Salva (insert ou update) um jogador. Retorna o ID gerado/existente. */
    public function save(Jogador $jogador): int;

    /** Busca jogador por ID. Retorna null se não encontrar. */
    public function find(int $id): ?Jogador;

    /** Busca jogador pelo nome exato. */
    public function findByNome(string $nome): ?Jogador;

    /** Retorna todos os jogadores. */
    public function findAll(): array;

    /** Remove um jogador pelo ID. */
    public function delete(int $id): bool;

    /** Atualiza apenas o progresso (cena, sync, suspeita, frags, flags). */
    public function updateProgresso(Jogador $jogador): bool;

    /** Adiciona um final ou morte ao mural do jogador. */
    public function adicionarFinal(int $id, string $chave, string $tipo): bool;
}
