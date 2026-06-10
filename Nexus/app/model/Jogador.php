<?php

declare(strict_types=1);

namespace Nexus\Model;

/**
 * PASSO 2 — Model / Entidade
 * Apenas representa os dados de um jogador. Zero SQL aqui.
 */
class Jogador
{
    public function __construct(
        public readonly ?int    $id            = null,
        public string           $nome          = '',
        public string           $genero        = 'f',   // f | m | n
        public int              $sync          = 75,
        public int              $suspeita      = 0,
        public int              $frags         = 0,
        public string           $cena_atual    = 'room_start',
        public string           $path_escolhido = '',
        public array            $flags         = [],
        public array            $inventario    = [],
        public array            $lore          = [],
        public array            $finais_desbloqueados = [],
        public array            $mortes_desbloqueadas = [],
        public ?string          $criado_em     = null,
        public ?string          $atualizado_em = null,
    ) {}

    /**
     * Métodos mágicos para acesso dinâmico (compatibilidade com legado).
     */
    public function __get(string $name): mixed
    {
        return property_exists($this, $name) ? $this->$name : null;
    }

    public function __isset(string $name): bool
    {
        return property_exists($this, $name);
    }

    /**
     * Valida gênero permitido.
     */
    public function generoValido(): bool
    {
        return in_array($this->genero, ['f', 'm', 'n'], true);
    }

    /**
     * Retorna arrays JSON como string para persistência.
     */
    public function flagsJson(): string
    {
        return json_encode($this->flags, JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    public function inventarioJson(): string
    {
        return json_encode($this->inventario, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    public function loreJson(): string
    {
        return json_encode($this->lore, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    public function finaisJson(): string
    {
        return json_encode($this->finais_desbloqueados, JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    public function mortesJson(): string
    {
        return json_encode($this->mortes_desbloqueadas, JSON_UNESCAPED_UNICODE) ?: '[]';
    }
}
