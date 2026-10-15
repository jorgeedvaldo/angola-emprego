<?php

namespace App\Exceptions;

use RuntimeException;
use Throwable;

/**
 * A base de dados não respondeu e não havia cópia em cache para servir.
 *
 * É o único erro que faz o site mostrar a página de indisponibilidade. Um erro
 * de programação continua a ser um erro de programação e tem de aparecer como
 * tal — esconder tudo atrás de uma página bonita era a maneira mais rápida de
 * nunca mais darmos por uma avaria a sério.
 */
class BaseDeDadosIndisponivel extends RuntimeException
{
    public static function de(Throwable $anterior): self
    {
        return new self('A base de dados não está a responder.', 0, $anterior);
    }
}
