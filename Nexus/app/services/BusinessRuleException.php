<?php

declare(strict_types=1);

namespace Nexus\Exception;

use RuntimeException;

/**
 * PASSO 3 — Exceção de Regra de Negócio
 * Lançada pelo Service quando uma regra falha.
 * O Controller captura e exibe a mensagem ao usuário.
 */
class BusinessRuleException extends RuntimeException
{
    public function __construct(string $message, private readonly string $campo = '')
    {
        parent::__construct($message);
    }

    /** Campo do formulário que gerou o erro (para highlight na view). */
    public function getCampo(): string
    {
        return $this->campo;
    }
}
