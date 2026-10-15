<?php

namespace App\Console\Commands;

use App\Services\CircuitoBaseDeDados;
use App\Services\DatabaseFallbackCache;
use App\Http\Middleware\CacheDePaginasPublicas;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * `php artisan resiliencia` — ver se a rede de segurança está armada, e armá-la.
 *
 * Serve para responder à pergunta que se faz quando o site está lento: "isto
 * está a servir da cache ou está a ir ao MySQL?".
 */
class EstadoDaResiliencia extends Command
{
    protected $signature = 'resiliencia
                            {--limpar : Deita fora as cópias normais e a cache de HTML, deixando as de emergência}
                            {--fechar : Fecha o disjuntor à força, para voltar a tentar já}';

    protected $description = 'Mostra o estado da cache e do disjuntor da base de dados';

    /** As chaves que seguram as páginas públicas de pé. */
    private const CHAVES = [
        'latest_jobs_50' => 'Últimas vagas',
        'latest_posts_50' => 'Últimos artigos',
        'categories_all' => 'Categorias',
        'categories_with_jobs' => 'Categorias com contagem',
        'top_empresas' => 'Empresas que mais contratam',
        'total_vagas' => 'Total de vagas',
        'total_posts' => 'Total de artigos',
        'sitemap' => 'Sitemap',
    ];

    public function handle(CircuitoBaseDeDados $circuito, DatabaseFallbackCache $cache): int
    {
        if ($this->option('fechar')) {
            $circuito->esquecer();
            $this->info('Disjuntor fechado à força.');
        }

        if ($this->option('limpar')) {
            foreach (array_keys(self::CHAVES) as $chave) {
                $cache->esquecerANormal($chave);
            }

            CacheDePaginasPublicas::invalidar();
            $this->info('Cópias normais e cache de HTML deitadas fora. As de emergência ficaram.');
        }

        $this->linha('Loja de cache', config('resiliencia.loja'));
        $this->linha('Cópia normal', config('resiliencia.normal') . ' s');
        $this->linha('Cópia de emergência', config('resiliencia.emergencia') . ' s');
        $this->linha('Cache de HTML', config('resiliencia.html.activa') ? 'ligada' : 'desligada');
        $this->linha('Disjuntor', $circuito->aberto() ? '<fg=red>ABERTO</> (não se está a tentar ligar)' : '<fg=green>fechado</> (normal)');
        $this->linha('Base de dados', $this->bateNaPorta());

        $this->newLine();
        $this->table(
            ['Dados', 'Cópia de emergência'],
            collect(self::CHAVES)->map(fn ($nome, $chave) => [
                $nome,
                $cache->temCopiaDeEmergencia($chave) ? '<fg=green>sim</>' : '<fg=yellow>ainda não</>',
            ])->values()->all()
        );

        $this->newLine();
        $this->line('  Uma cópia de emergência em falta quer dizer só que aquela página ainda');
        $this->line('  não foi visitada desde a última limpeza. Visite-a uma vez e ela aparece.');

        return self::SUCCESS;
    }

    private function bateNaPorta(): string
    {
        try {
            DB::connection()->getPdo();

            return '<fg=green>responde</>';
        } catch (\Throwable $e) {
            return '<fg=red>não responde</>';
        }
    }

    private function linha(string $nome, string $valor): void
    {
        // O sprintf conta bytes e os acentos valem dois: alinhar à mão.
        $etiqueta = $nome . ':';
        $espacos = str_repeat(' ', max(1, 24 - mb_strlen($etiqueta)));

        $this->line('  <comment>' . $etiqueta . '</comment>' . $espacos . $valor);
    }
}
