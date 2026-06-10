-- ══════════════════════════════════════════════════
--  NEXUS ENTERPRISE — Script do Banco de Dados
--  Execute no MySQL antes de rodar o servidor PHP
-- ══════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS nexus_game
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE nexus_game;

-- ── Tabela principal de jogadores ──────────────────
CREATE TABLE IF NOT EXISTS jogadores (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Identificação
    nome                 VARCHAR(16)  NOT NULL,
    genero               ENUM('f','m','n') NOT NULL DEFAULT 'f',

    -- Status do jogo
    sync                 TINYINT UNSIGNED NOT NULL DEFAULT 75,
    suspeita             TINYINT UNSIGNED NOT NULL DEFAULT 0,
    frags                SMALLINT UNSIGNED NOT NULL DEFAULT 0,

    -- Progresso narrativo
    cena_atual           VARCHAR(64)  NOT NULL DEFAULT 'room_start',
    path_escolhido       VARCHAR(32)  NOT NULL DEFAULT '',

    -- Estado serializado (JSON)
    flags                JSON         NOT NULL DEFAULT ('{}'),
    inventario           JSON         NOT NULL DEFAULT ('[]'),
    lore                 JSON         NOT NULL DEFAULT ('[]'),

    -- Mural de finais e mortes
    finais_desbloqueados JSON         NOT NULL DEFAULT ('[]'),
    mortes_desbloqueadas JSON         NOT NULL DEFAULT ('[]'),

    -- Timestamps
    criado_em            TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em        TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_nome         (nome),
    INDEX idx_atualizado   (atualizado_em)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
