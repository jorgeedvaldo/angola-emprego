<?php

namespace Tests\Unit;

use App\Exceptions\BaseDeDadosIndisponivel;
use App\Services\CircuitoBaseDeDados;
use App\Services\DatabaseFallbackCache;
use App\Support\FalhaDeLigacao;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use PDOException;
use RuntimeException;
use Tests\TestCase;

/**
 * A rede que segura o site quando o MySQL recusa ligações.
 *
 * O alojamento dá 30 ligações a este utilizador. Quando se esgotam, o MySQL
 * devolve o erro 1226 e, sem isto, cada página virava um 500.
 */
class ResilienciaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::store(config('resiliencia.loja'))->flush();
    }

    // ------------------------------------------------- conhecer o erro certo

    /** O erro deste alojamento, tal e qual como chega. */
    private function erro1226(): QueryException
    {
        return $this->queryException(
            '42000',
            1226,
            "User 'yqqnyhrgnt_edv' has exceeded the 'max_user_connections' resource (current value: 30)"
        );
    }

    private function queryException(string $estado, int $numero, string $texto): QueryException
    {
        $pdo = new PDOException("SQLSTATE[$estado] [$numero] $texto");
        $pdo->errorInfo = [$estado, $numero, $texto];

        return new QueryException('select * from `jobs`', [], $pdo);
    }

    public function test_it_recognises_the_max_user_connections_error()
    {
        $this->assertTrue(FalhaDeLigacao::e($this->erro1226()));
    }

    public function test_it_recognises_the_other_ways_mysql_says_it_is_not_there()
    {
        foreach ([1040, 1203, 2002, 2003, 2006, 2013] as $numero) {
            $this->assertTrue(
                FalhaDeLigacao::e($this->queryException('HY000', $numero, 'sem ligação')),
                "O erro $numero devia contar como falta de ligação."
            );
        }
    }

    /**
     * O que importa aqui é o "não": um erro de programação não pode ser
     * confundido com falta de ligação, ou a avaria fica escondida atrás de uma
     * página bonita e ninguém dá por ela.
     */
    public function test_a_programming_mistake_is_not_a_connection_failure()
    {
        $this->assertFalse(FalhaDeLigacao::e($this->queryException('42000', 1064, 'You have an error in your SQL syntax')));
        $this->assertFalse(FalhaDeLigacao::e($this->queryException('42S02', 1146, "Table 'x.y' doesn't exist")));
        $this->assertFalse(FalhaDeLigacao::e(new RuntimeException('qualquer coisa')));
    }

    /**
     * Um erro sem número, como o que o PDO atira quando falha a ligar antes de
     * haver consulta nenhuma, é reconhecido pela frase.
     */
    public function test_it_recognises_a_connection_failure_that_carries_no_error_number()
    {
        $sem_numero = new PDOException('SQLSTATE[HY000] [2002] Connection refused');

        $this->assertTrue(FalhaDeLigacao::e($sem_numero));
        $this->assertTrue(FalhaDeLigacao::e(new PDOException("User 'x' has exceeded the 'max_user_connections' resource")));
        $this->assertFalse(FalhaDeLigacao::e(new PDOException('SQLSTATE[42S22]: Column not found: 1054')));
    }

    /** O 1226 traz o SQLSTATE 42000, que é a classe da sintaxe — tem de ser o número a decidir. */
    public function test_the_number_decides_and_not_the_sqlstate_class()
    {
        $sintaxe = $this->queryException('42000', 1064, 'erro de sintaxe');
        $ligacao = $this->queryException('42000', 1226, 'sem ligações');

        $this->assertFalse(FalhaDeLigacao::e($sintaxe));
        $this->assertTrue(FalhaDeLigacao::e($ligacao));
    }

    // ------------------------------------------------------------ a cache

    private function cache(): DatabaseFallbackCache
    {
        return app(DatabaseFallbackCache::class);
    }

    public function test_with_the_database_up_it_queries_and_keeps_both_copies()
    {
        $resultado = $this->cache()->lembrar('vagas', 60, fn () => 'dados frescos');

        $this->assertSame('dados frescos', $resultado);
        $this->assertTrue($this->cache()->temCopiaDeEmergencia('vagas'));
    }

    /** Com a cache cheia não se volta à base de dados. */
    public function test_a_warm_cache_does_not_go_back_to_the_database()
    {
        $idas = 0;

        $consulta = function () use (&$idas) {
            $idas++;

            return 'dados';
        };

        $this->cache()->lembrar('vagas', 60, $consulta);
        $this->cache()->lembrar('vagas', 60, $consulta);
        $this->cache()->lembrar('vagas', 60, $consulta);

        $this->assertSame(1, $idas);
    }

    /**
     * O caso que motivou tudo isto: a cópia normal expirou, o MySQL responde
     * 1226, e a página tem de abrir na mesma.
     */
    public function test_when_the_normal_copy_expired_and_mysql_refuses_the_emergency_copy_serves()
    {
        $this->cache()->lembrar('vagas', 60, fn () => 'o que havia ontem');

        // A cópia normal expira; a de emergência fica.
        $this->cache()->esquecerANormal('vagas');

        $resultado = $this->cache()->lembrar('vagas', 60, fn () => throw $this->erro1226());

        $this->assertSame('o que havia ontem', $resultado);
    }

    /** A cópia de emergência não pode morrer com a normal. */
    public function test_forgetting_the_normal_copy_leaves_the_emergency_one_standing()
    {
        $this->cache()->lembrar('vagas', 60, fn () => 'dados');

        $this->cache()->esquecerANormal('vagas');

        $this->assertTrue($this->cache()->temCopiaDeEmergencia('vagas'));
    }

    public function test_without_any_copy_and_without_a_database_it_gives_up_loudly()
    {
        $this->expectException(BaseDeDadosIndisponivel::class);

        $this->cache()->lembrar('nunca-vista', 60, fn () => throw $this->erro1226());
    }

    /** Um erro de programação continua a rebentar, com cache ou sem ela. */
    public function test_a_programming_mistake_still_blows_up()
    {
        $this->cache()->lembrar('vagas', 60, fn () => 'dados');
        $this->cache()->esquecerANormal('vagas');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('isto é um erro meu');

        $this->cache()->lembrar('vagas', 60, fn () => throw new RuntimeException('isto é um erro meu'));
    }

    /** O null é um resultado legítimo e não pode ser lido como "não está em cache". */
    public function test_a_null_result_is_remembered_as_a_result()
    {
        $idas = 0;

        $consulta = function () use (&$idas) {
            $idas++;

            return null;
        };

        $this->assertNull($this->cache()->lembrar('vazio', 60, $consulta));
        $this->assertNull($this->cache()->lembrar('vazio', 60, $consulta));

        $this->assertSame(1, $idas);
    }

    // --------------------------------------------------------- o disjuntor

    private function circuito(): CircuitoBaseDeDados
    {
        return app(CircuitoBaseDeDados::class);
    }

    public function test_the_breaker_opens_after_the_failures_add_up()
    {
        $circuito = $this->circuito();

        for ($i = 1; $i < config('resiliencia.falhas_para_abrir'); $i++) {
            $circuito->falha();
            $this->assertTrue($circuito->fechado(), "Não devia abrir à falha $i.");
        }

        $circuito->falha();

        $this->assertTrue($circuito->aberto());
    }

    /**
     * Com o disjuntor aberto não se tenta ligar. Cada tentativa ocupava uma
     * das 30 ligações e atrasava a recuperação.
     */
    public function test_with_the_breaker_open_nobody_touches_the_database()
    {
        $this->cache()->lembrar('vagas', 60, fn () => 'o que havia ontem');
        $this->cache()->esquecerANormal('vagas');

        $circuito = $this->circuito();

        for ($i = 0; $i < config('resiliencia.falhas_para_abrir'); $i++) {
            $circuito->falha();
        }

        $tentativas = 0;

        // Dez visitantes ao mesmo tempo, como numa hora de ponta.
        for ($i = 0; $i < 10; $i++) {
            $resultado = $this->cache()->lembrar('vagas', 60, function () use (&$tentativas) {
                $tentativas++;

                return 'fresco';
            });

            $this->assertSame('o que havia ontem', $resultado);
        }

        $this->assertSame(0, $tentativas, 'Ninguém devia ter tentado ligar-se com o disjuntor aberto.');
    }

    public function test_when_the_database_comes_back_everything_goes_back_to_normal()
    {
        $circuito = $this->circuito();

        for ($i = 0; $i < config('resiliencia.falhas_para_abrir'); $i++) {
            $circuito->falha();
        }

        $this->assertTrue($circuito->aberto());

        // Passou o tempo de descanso: uma requisição vai sondar.
        $this->travel(config('resiliencia.descanso') + 1)->seconds();

        $this->assertTrue($circuito->fechado(), 'Passado o descanso, alguém tem de poder tentar.');

        $resultado = $this->cache()->lembrar('vagas-novas', 60, fn () => 'dados frescos');

        $this->assertSame('dados frescos', $resultado);
        $this->assertTrue($circuito->fechado());
    }

    /** Passado o descanso, sonda **uma** requisição, não dezenas. */
    public function test_only_one_request_probes_the_recovery()
    {
        $circuito = $this->circuito();

        for ($i = 0; $i < config('resiliencia.falhas_para_abrir'); $i++) {
            $circuito->falha();
        }

        $this->travel(config('resiliencia.descanso') + 1)->seconds();

        $passaram = 0;

        // Dez requisições são dez instâncias: cada processo PHP tem a sua.
        for ($i = 0; $i < 10; $i++) {
            if ((new CircuitoBaseDeDados())->fechado()) {
                $passaram++;
            }
        }

        $this->assertSame(1, $passaram, 'Só uma requisição devia ir sondar a recuperação.');
    }
}
