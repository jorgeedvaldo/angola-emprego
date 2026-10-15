<?php

namespace App\Services;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * O disjuntor da base de dados.
 *
 * Quando o MySQL recusa ligações, o pior que se pode fazer é mandar cada
 * visitante tentar outra vez: cada tentativa ocupa uma das 30 ligações e
 * atrasa a recuperação. Ao fim de algumas falhas seguidas este disjuntor
 * abre, e enquanto estiver aberto ninguém tenta — serve-se o que está em
 * cache.
 *
 * Passado o tempo de descanso, deixa passar **uma** requisição para ir ver se
 * já há ligação. Uma só: é para isso que serve o bloqueio. Se dezenas fossem
 * ao mesmo tempo, a recuperação era o próximo pico.
 *
 * O estado vive na cache de ficheiros, que é partilhada entre os processos PHP
 * do alojamento e não depende do MySQL — se dependesse não servia para nada.
 */
class CircuitoBaseDeDados
{
    private const FALHAS = 'bd:falhas';
    private const ABERTO = 'bd:aberto-ate';
    private const SONDA = 'bd:sonda';

    /** Esta requisição já ganhou a sonda e não volta a disputá-la. */
    private bool $sondaGanha = false;

    public function __construct(private ?Repository $cache = null)
    {
        $this->cache = $cache ?: Cache::store(config('resiliencia.loja'));
    }

    /** O disjuntor está aberto: não se tenta ligar à base de dados. */
    public function aberto(): bool
    {
        $ate = $this->cache->get(self::ABERTO);

        if ($ate === null) {
            $this->sondaGanha = false;

            return false;
        }

        if (now()->getTimestamp() >= (int) $ate) {
            // Passou o descanso. Uma requisição — e só uma — vai sondar. Quem
            // já ganhou a sonda não volta a disputá-la: uma página faz várias
            // consultas, e seria estranho a primeira correr e as outras não.
            if ($this->sondaGanha) {
                return false;
            }

            return !($this->sondaGanha = $this->ganhouASonda());
        }

        return true;
    }

    public function fechado(): bool
    {
        return !$this->aberto();
    }

    /** Correu bem: o circuito fecha e a contagem de falhas recomeça. */
    public function sucesso(): void
    {
        if ($this->cache->get(self::ABERTO) !== null) {
            Log::info('[resiliencia] A base de dados voltou a responder.');
        }

        $this->sondaGanha = false;
        $this->cache->forget(self::FALHAS);
        $this->cache->forget(self::ABERTO);
    }

    /** Correu mal. Ao fim de N falhas seguidas, o circuito abre. */
    public function falha(): void
    {
        $this->sondaGanha = false;

        $falhas = (int) $this->cache->get(self::FALHAS, 0) + 1;
        $limite = (int) config('resiliencia.falhas_para_abrir');

        $this->cache->put(self::FALHAS, $falhas, config('resiliencia.janela_de_falhas'));

        if ($falhas < $limite) {
            return;
        }

        $descanso = (int) config('resiliencia.descanso');

        $this->cache->put(self::ABERTO, now()->getTimestamp() + $descanso, $descanso + 60);

        Log::warning('[resiliencia] A base de dados falhou :falhas vezes seguidas; a parar as tentativas durante :descanso s.', [
            'falhas' => $falhas,
            'descanso' => $descanso,
        ]);
    }

    /** Para os testes e para um `artisan` de emergência. */
    public function esquecer(): void
    {
        $this->cache->forget(self::FALHAS);
        $this->cache->forget(self::ABERTO);
        $this->cache->forget(self::SONDA);
    }

    /**
     * Só uma requisição passa a sondar de cada vez. Sem bloqueio na loja de
     * cache, o add() atómico serve de substituto.
     */
    private function ganhouASonda(): bool
    {
        return $this->cache->add(self::SONDA, 1, (int) config('resiliencia.espera_da_sonda'));
    }
}
