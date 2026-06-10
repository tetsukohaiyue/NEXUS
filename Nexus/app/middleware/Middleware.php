<?php

declare(strict_types=1);

namespace Nexus\Middleware;

/**
 * PASSO 5 — Middleware de Segurança
 * Sanitiza inputs, barra XSS e valida Content-Type antes de chegar ao Controller.
 */
class Middleware
{
    /**
     * Executa verificações antes de qualquer rota POST de criação de jogador.
     * Aborta com JSON de erro se algo for inválido.
     */
    public static function handleIniciar(): void
    {
        // 1. Apenas POST é aceito nessa rota
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::abort(405, 'Método não permitido.');
        }

        // 2. Sanitiza e valida 'nome' — barra XSS com filter_input
        $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_SPECIAL_CHARS);

        if ($nome === null || $nome === false || trim($nome) === '') {
            self::abort(400, 'O campo nome é obrigatório.', 'nome');
        }

        if (mb_strlen(trim($nome)) > 16) {
            self::abort(400, 'O nome deve ter no máximo 16 caracteres.', 'nome');
        }

        // 3. Sanitiza 'genero'
        $genero = filter_input(INPUT_POST, 'genero', FILTER_SANITIZE_SPECIAL_CHARS);
        if (!in_array($genero, ['f', 'm', 'n'], true)) {
            self::abort(400, 'Gênero inválido.', 'genero');
        }

        // Sobrescreve $_POST com valores sanitizados
        $_POST['nome']   = strip_tags(trim($nome));
        $_POST['genero'] = $genero;
    }

    /**
     * Middleware para rotas JSON (autosave, final, etc).
     * Verifica Content-Type e que o corpo é JSON válido.
     */
    public static function handleJson(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::abort(405, 'Método não permitido.');
        }

        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') === false) {
            self::abort(415, 'Content-Type deve ser application/json.');
        }

        $body = file_get_contents('php://input');
        if (json_decode($body) === null && json_last_error() !== JSON_ERROR_NONE) {
            self::abort(400, 'Corpo da requisição não é um JSON válido.');
        }
    }

    /**
     * Middleware para rotas GET — verifica parâmetro 'id'.
     */
    public static function handleGet(): void
    {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id <= 0) {
            self::abort(400, 'Parâmetro id inválido ou ausente.');
        }
    }

    /**
     * Aborta a execução com resposta JSON de erro.
     */
    private static function abort(int $status, string $mensagem, string $campo = ''): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        echo json_encode([
            'success' => false,
            'erro'    => $mensagem,
            'campo'   => $campo,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
