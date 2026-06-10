<?php

declare(strict_types=1);

namespace Nexus\Controller;

use Nexus\Service\JogadorService;
use Nexus\Exception\BusinessRuleException;

/**
 * PASSO 4 — Controller Enxuto
 * Recebe dependências pelo construtor.
 * Métodos contêm APENAS try/catch — zero validação de regra de negócio aqui.
 * Retorna JSON (API para o jogo em HTML/JS).
 */
class JogadorController
{
    // Recebe o Service pelo construtor (Injeção de Dependência)
    public function __construct(
        private readonly JogadorService $service
    ) {}

    // ─────────────────────────────────────────────
    // POST /jogador/iniciar
    // ─────────────────────────────────────────────
    public function iniciar(): void
    {
        try {
            $nome   = $_POST['nome']   ?? '';
            $genero = $_POST['genero'] ?? 'f';

            $jogador = $this->service->iniciarJogo($nome, $genero);

            $this->json([
                'success'  => true,
                'jogador'  => $this->serialize($jogador),
            ]);
        } catch (BusinessRuleException $e) {
            $this->json([
                'success' => false,
                'erro'    => $e->getMessage(),
                'campo'   => $e->getCampo(),
            ], 422);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // POST /jogador/progresso
    // ─────────────────────────────────────────────
    public function salvarProgresso(): void
    {
        try {
            $body = $this->jsonBody();

            $ok = $this->service->salvarProgresso(
                id:         (int) ($body['id'] ?? 0),
                cena:       $body['cena']       ?? 'room_start',
                sync:       (int) ($body['sync']       ?? 75),
                suspeita:   (int) ($body['suspeita']   ?? 0),
                frags:      (int) ($body['frags']      ?? 0),
                path:       $body['path']       ?? '',
                flags:      $body['flags']      ?? [],
                inventario: $body['inventario'] ?? [],
                lore:       $body['lore']       ?? [],
            );

            $this->json(['success' => $ok]);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'erro' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // POST /jogador/final
    // ─────────────────────────────────────────────
    public function desbloquearFinal(): void
    {
        try {
            $body = $this->jsonBody();

            $ok = $this->service->desbloquearFinal(
                id:    (int) ($body['id']    ?? 0),
                chave: $body['chave'] ?? '',
                tipo:  $body['tipo']  ?? 'ending',
            );

            $this->json(['success' => $ok]);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'erro' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // GET /jogador/mural?id=X
    // ─────────────────────────────────────────────
    public function getMural(): void
    {
        try {
            $id    = (int) ($_GET['id'] ?? 0);
            $mural = $this->service->getMural($id);
            $this->json(['success' => true, 'mural' => $mural]);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'erro' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // GET /jogador?id=X
    // ─────────────────────────────────────────────
    public function buscar(): void
    {
        try {
            $id     = (int) ($_GET['id'] ?? 0);
            $jogador = $this->service->buscarJogador($id);
            $this->json(['success' => true, 'jogador' => $this->serialize($jogador)]);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'erro' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // DELETE /jogador?id=X
    // ─────────────────────────────────────────────
    public function deletar(): void
    {
        try {
            $id = (int) ($_GET['id'] ?? 0);
            $ok = $this->service->deletarJogador($id);
            $this->json(['success' => $ok]);
        } catch (BusinessRuleException $e) {
            $this->json(['success' => false, 'erro' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            error_log('[NEXUS CONTROLLER] ' . $e->getMessage());
            $this->json(['success' => false, 'erro' => 'Erro interno.'], 500);
        }
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw ?: '{}', true) ?: [];
    }

    private function serialize(\Nexus\Model\Jogador $j): array
    {
        return [
            'id'                   => $j->id,
            'nome'                 => $j->nome,
            'genero'               => $j->genero,
            'sync'                 => $j->sync,
            'suspeita'             => $j->suspeita,
            'frags'                => $j->frags,
            'cena_atual'           => $j->cena_atual,
            'path_escolhido'       => $j->path_escolhido,
            'flags'                => $j->flags,
            'inventario'           => $j->inventario,
            'lore'                 => $j->lore,
            'finais_desbloqueados' => $j->finais_desbloqueados,
            'mortes_desbloqueadas' => $j->mortes_desbloqueadas,
            'criado_em'            => $j->criado_em,
            'atualizado_em'        => $j->atualizado_em,
        ];
    }
}
