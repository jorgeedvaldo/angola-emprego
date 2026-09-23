<?php

namespace App\Services\Cv;

/**
 * Qual o motor que pontua os CVs no analisador público.
 *
 * Só existem dois, e a troca faz-se por uma linha do .env:
 *
 *   CV_ANALYZER_ENGINE=vectores   embeddings + palavras-chave, requisito a
 *                                 requisito — o que sempre lá esteve
 *   CV_ANALYZER_ENGINE=jev        uma pergunta ao JEV por CV, e a
 *                                 probabilidade que ele devolve é a pontuação
 *
 * Voltar atrás é apagar a linha: o caminho dos vectores não foi tocado e volta
 * a correr exactamente como corria. É por isso que isto é um interruptor e não
 * uma substituição — se o JEV não der o que se espera, não há nada para desfazer.
 *
 * A análise de candidaturas das empresas (CompanyController), que grava
 * pontuações na base de dados, nunca passa por aqui: fica sempre nos vectores.
 */
class CvEngine
{
    public const VECTORES = 'vectores';
    public const JEV = 'jev';

    /**
     * O JEV só entra quando foi pedido *e* está configurado. Sem a chave, uma
     * configuração a meio deixaria o analisador em baixo; assim cai no motor
     * antigo, que funciona.
     */
    public static function jevActivo(): bool
    {
        return self::escolhido() === self::JEV && (new JevClient())->configurado();
    }

    public static function escolhido(): string
    {
        return strtolower(trim((string) config('services.cv_analyzer_engine', self::VECTORES)))
            === self::JEV ? self::JEV : self::VECTORES;
    }

    /** O motor que está mesmo a ser usado, para o log e para os testes. */
    public static function activo(): string
    {
        return self::jevActivo() ? self::JEV : self::VECTORES;
    }
}
