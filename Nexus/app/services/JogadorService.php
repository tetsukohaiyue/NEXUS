<?php

declare(strict_types=1);

namespace Nexus\Service;

use Nexus\Model\Jogador;
use Nexus\Repository\IJogadorRepository;
use Nexus\Exception\BusinessRuleException;

/**
 * PASSO 3 — Service com Injeção de Dependência
 * Contém APENAS regras de negócio. Não instancia nada internamente.
 * Recebe o repositório pelo construtor (Injeção de Dependência).
 */
class JogadorService
{
    // REGRA DE OURO: Service não instancia repositório — recebe pelo construtor
    public function __construct(
        private readonly IJogadorRepository $repository
    ) {}

    // ─────────────────────────────────────────────
    // INICIAR JOGO (criar ou retomar sessão)
    // ─────────────────────────────────────────────
    public function iniciarJogo(string $nome, string $genero): Jogador
    {
        // Regra: nome obrigatório
        if (empty(trim($nome))) {
            throw new BusinessRuleException('O nome do agente é obrigatório.', 'nome');
        }

        // Regra: nome máximo 16 caracteres
        if (mb_strlen($nome) > 16) {
            throw new BusinessRuleException('O nome deve ter no máximo 16 caracteres.', 'nome');
        }

        // Regra: gênero válido
        if (!in_array($genero, ['f', 'm', 'n'], true)) {
            throw new BusinessRuleException('Gênero inválido.', 'genero');
        }

        // Se já existe jogador com esse nome, retoma a sessão
        $existente = $this->repository->findByNome($nome);
        if ($existente) {
            return $existente;
        }

        // Cria novo jogador
        $jogador = new Jogador(nome: $nome, genero: $genero);
        $id = $this->repository->save($jogador);

        // Retorna com ID preenchido
        return $this->repository->find($id)
            ?? throw new BusinessRuleException('Erro ao criar jogador.');
    }

    // ─────────────────────────────────────────────
    // SALVAR PROGRESSO (autosave do jogo)
    // ─────────────────────────────────────────────
    public function salvarProgresso(
        int    $id,
        string $cena,
        int    $sync,
        int    $suspeita,
        int    $frags,
        string $path,
        array  $flags,
        array  $inventario,
        array  $lore
    ): bool {
        $jogador = $this->repository->find($id);

        if (!$jogador) {
            throw new BusinessRuleException('Jogador não encontrado.');
        }

        // Regra: sync entre 0 e 100
        if ($sync < 0 || $sync > 100) {
            throw new BusinessRuleException('Valor de sync inválido.');
        }

        // Regra: suspeita entre 0 e 100
        if ($suspeita < 0 || $suspeita > 100) {
            throw new BusinessRuleException('Valor de suspeita inválido.');
        }

        $jogador->sync          = $sync;
        $jogador->suspeita      = $suspeita;
        $jogador->frags         = max(0, $frags);
        $jogador->cena_atual    = $cena;
        $jogador->path_escolhido = $path;
        $jogador->flags         = $flags;
        $jogador->inventario    = $inventario;
        $jogador->lore          = $lore;

        return $this->repository->updateProgresso($jogador);
    }

    // ─────────────────────────────────────────────
    // DESBLOQUEAR FINAL / MORTE NO MURAL
    // ─────────────────────────────────────────────
    public function desbloquearFinal(int $id, string $chave, string $tipo): bool
    {
        if (!in_array($tipo, ['ending', 'death'], true)) {
            throw new BusinessRuleException('Tipo de final inválido.');
        }

        $finaisValidos = [
            'demissao', 'eliminacao', 'sacrificio', 'verdadeiro', 'corpo',
            'morte_elevador', 'morte_suspeita', 'morte_boss',
        ];

        if (!in_array($chave, $finaisValidos, true)) {
            throw new BusinessRuleException('Chave de final inválida.');
        }

        if (!$this->repository->find($id)) {
            throw new BusinessRuleException('Jogador não encontrado.');
        }

        return $this->repository->adicionarFinal($id, $chave, $tipo);
    }

    // ─────────────────────────────────────────────
    // BUSCAR MURAL (finais + mortes do jogador)
    // ─────────────────────────────────────────────
    public function getMural(int $id): array
    {
        $jogador = $this->repository->find($id);

        if (!$jogador) {
            throw new BusinessRuleException('Jogador não encontrado.');
        }

        return [
            'endings' => $jogador->finais_desbloqueados,
            'deaths'  => $jogador->mortes_desbloqueadas,
        ];
    }

    // ─────────────────────────────────────────────
    // BUSCAR JOGADOR
    // ─────────────────────────────────────────────
    public function buscarJogador(int $id): Jogador
    {
        $jogador = $this->repository->find($id);

        if (!$jogador) {
            throw new BusinessRuleException('Jogador não encontrado.');
        }

        return $jogador;
    }

    // ─────────────────────────────────────────────
    // DELETAR JOGADOR (reset)
    // ─────────────────────────────────────────────
    public function deletarJogador(int $id): bool
    {
        if (!$this->repository->find($id)) {
            throw new BusinessRuleException('Jogador não encontrado para exclusão.');
        }

        return $this->repository->delete($id);
    }
}
