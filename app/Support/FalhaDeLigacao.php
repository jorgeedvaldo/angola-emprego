<?php

namespace App\Support;

use App\Exceptions\BaseDeDadosIndisponivel;
use PDOException;
use Throwable;

/**
 * Distingue "a base de dados não está a responder" de "o SQL está mal escrito".
 *
 * A diferença é toda: a primeira serve-se com a cópia em cache, a segunda tem
 * de rebentar para alguém a ir corrigir.
 */
class FalhaDeLigacao
{
    /**
     * Números de erro do MySQL que dizem que não há ligação a haver.
     *
     * O 1226 é o deste alojamento: o utilizador passou o max_user_connections.
     * Vem com SQLSTATE 42000, que é a classe dos erros de sintaxe — por isso é
     * preciso olhar para o número, não para a classe.
     */
    private const NUMEROS = [
        1040,  // Too many connections (o servidor todo)
        1203,  // User already has more than 'max_user_connections' active
        1226,  // User has exceeded the 'max_user_connections' resource
        2002,  // Can't connect to local MySQL server
        2003,  // Can't connect to MySQL server
        2006,  // MySQL server has gone away
        2013,  // Lost connection to MySQL server during query
    ];

    /** A classe 08 do SQLSTATE é, por norma, "connection exception". */
    private const CLASSES_SQLSTATE = ['08'];

    /**
     * Último recurso, para quando o erro chega sem número nenhum — acontece
     * quando o PDO falha a ligar antes de haver sequer uma consulta.
     *
     * São frases que não querem dizer mais nada senão "não há ligação". Nenhum
     * erro de programação as produz, que é o que torna isto seguro.
     */
    private const FRASES = [
        'max_user_connections',
        'too many connections',
        'server has gone away',
        'lost connection',
        'connection refused',
        'could not connect',
        'unable to open database file',
        'no connection to the server',
    ];

    public static function e(?Throwable $erro): bool
    {
        while ($erro !== null) {
            if (self::oErroDiz($erro)) {
                return true;
            }

            $erro = $erro->getPrevious();
        }

        return false;
    }

    /** Transforma o erro no nosso, mantendo o original como causa. */
    public static function converter(Throwable $erro): BaseDeDadosIndisponivel
    {
        return BaseDeDadosIndisponivel::de($erro);
    }

    private static function oErroDiz(Throwable $erro): bool
    {
        if ($erro instanceof BaseDeDadosIndisponivel) {
            return true;
        }

        $numero = self::numeroDoDriver($erro);

        if ($numero !== null && in_array($numero, self::NUMEROS, true)) {
            return true;
        }

        $estado = self::sqlstate($erro);

        if ($estado !== null && in_array(substr($estado, 0, 2), self::CLASSES_SQLSTATE, true)) {
            return true;
        }

        return self::aFraseDiz($erro);
    }

    private static function aFraseDiz(Throwable $erro): bool
    {
        if (!$erro instanceof PDOException) {
            return false;
        }

        $mensagem = strtolower($erro->getMessage());

        foreach (self::FRASES as $frase) {
            if (str_contains($mensagem, $frase)) {
                return true;
            }
        }

        return false;
    }

    /**
     * O número do MySQL vem no errorInfo[1] da QueryException e da PDOException.
     */
    private static function numeroDoDriver(Throwable $erro): ?int
    {
        $info = null;

        if ($erro instanceof PDOException) {
            $info = $erro->errorInfo;
        } elseif (property_exists($erro, 'errorInfo')) {
            $info = $erro->errorInfo;
        }

        if (is_array($info) && isset($info[1]) && is_numeric($info[1])) {
            return (int) $info[1];
        }

        return null;
    }

    private static function sqlstate(Throwable $erro): ?string
    {
        if ($erro instanceof PDOException) {
            $estado = $erro->errorInfo[0] ?? $erro->getCode();

            return is_string($estado) && $estado !== '' ? $estado : null;
        }

        if (property_exists($erro, 'errorInfo') && is_array($erro->errorInfo)) {
            return isset($erro->errorInfo[0]) ? (string) $erro->errorInfo[0] : null;
        }

        return null;
    }
}
