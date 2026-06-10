<?php

declare(strict_types=1);

namespace Nexus\Database;

use PDO;
use PDOException;
use RuntimeException;

/**
 * PASSO 1 — Configuração Base + Singleton PDO
 * Único responsável por ler o config.ini e entregar a conexão.
 * Nenhum outro arquivo do sistema conecta ao banco diretamente.
 */
final class Database
{
    private static ?PDO $instance = null;
    private static ?array $config  = null;

    // Impede instanciação externa
    private function __construct() {}
    private function __clone()     {}

    /**
     * Retorna a única instância do PDO (Singleton).
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        return self::$instance;
    }

    /**
     * Cria a conexão lendo o config.ini.
     */
    private static function createConnection(): PDO
    {
        $cfg = self::getConfig();

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['driver'],
            $cfg['host'],
            $cfg['port'],
            $cfg['dbname'],
            $cfg['charset']
        );

        try {
            $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return $pdo;
        } catch (PDOException $e) {
            // Nunca expõe detalhes ao usuário — só loga internamente
            error_log('[NEXUS DB ERROR] ' . $e->getMessage());
            throw new RuntimeException('Falha na conexão com o banco de dados.');
        }
    }

    /**
     * Lê e cacheia o config.ini.
     */
    private static function getConfig(): array
    {
        if (self::$config === null) {
            $path = __DIR__ . '/../../config/config.ini';

            if (!file_exists($path)) {
                throw new RuntimeException('Arquivo config.ini não encontrado.');
            }

            $ini = parse_ini_file($path, true);

            if ($ini === false || empty($ini['database'])) {
                throw new RuntimeException('config.ini inválido ou incompleto.');
            }

            self::$config = $ini['database'];
        }
        return self::$config;
    }
}
