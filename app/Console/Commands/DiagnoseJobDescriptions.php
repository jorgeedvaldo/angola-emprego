<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Support\JobRequirements;
use App\Support\KeywordMatcher;
use App\Support\PlainText;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Mede o leitor de requisitos contra os anúncios reais da base de dados.
 *
 * O algoritmo foi afinado com meia dúzia de anúncios escritos à mão, o que não
 * chega: cada empresa escreve a vaga à sua maneira e é isso que decide se
 * conseguimos ou não ler os requisitos. Este comando não altera nada — percorre
 * as vagas publicadas, diz em quantas é que o leitor funciona, e sobretudo
 * mostra os títulos de secção que ele ainda não conhece, que são a lista do que
 * falta corrigir.
 */
class DiagnoseJobDescriptions extends Command
{
    protected $signature = 'cv:diagnosticar
        {--limite=500 : Quantas vagas analisar, das mais recentes para trás}
        {--falhas=15 : Quantos títulos desconhecidos listar}
        {--exportar= : Ficheiro onde gravar as vagas que falharam, para análise}';

    protected $description = 'Mede a leitura de requisitos das vagas reais e aponta o que falta reconhecer';

    public function handle(): int
    {
        $limite = (int) $this->option('limite');

        $vagas = Job::query()
            ->select(['id', 'title', 'description'])
            ->orderByDesc('id')
            ->limit($limite)
            ->get();

        if ($vagas->isEmpty()) {
            $this->error('Não há vagas na base de dados.');

            return self::FAILURE;
        }

        $comRequisitos = 0;
        $contagens = [];
        $vocabulario = [];
        $semSeccao = [];
        $titulosDesconhecidos = [];

        foreach ($vagas as $vaga) {
            $linhas = JobRequirements::lines($vaga->description);

            if ($linhas !== []) {
                $comRequisitos++;
                $contagens[] = count($linhas);
            } else {
                $semSeccao[] = $vaga;

                foreach ($this->candidatosATitulo($vaga->description) as $candidato) {
                    $titulosDesconhecidos[$candidato] = ($titulosDesconhecidos[$candidato] ?? 0) + 1;
                }
            }

            $vocabulario[] = count(KeywordMatcher::extractKeywords($vaga->description));
        }

        $total = $vagas->count();

        $this->info(sprintf('Vagas analisadas: %d', $total));
        $this->line(sprintf(
            '  Com requisitos lidos: %d (%.0f%%) — estas dão checklist ao recrutador',
            $comRequisitos,
            100 * $comRequisitos / $total
        ));
        $this->line(sprintf(
            '  Sem secção reconhecida: %d (%.0f%%) — estas caem na comparação global',
            $total - $comRequisitos,
            100 * ($total - $comRequisitos) / $total
        ));

        if ($contagens !== []) {
            sort($contagens);
            $this->line(sprintf(
                '  Requisitos por vaga: mínimo %d, mediana %d, máximo %d',
                $contagens[0],
                $contagens[intdiv(count($contagens), 2)],
                end($contagens)
            ));
        }

        $this->line(sprintf(
            '  Termos extraídos por vaga: média %.1f',
            array_sum($vocabulario) / max(1, count($vocabulario))
        ));

        arsort($titulosDesconhecidos);
        $falhas = array_slice($titulosDesconhecidos, 0, (int) $this->option('falhas'), true);

        if ($falhas !== []) {
            $this->newLine();
            $this->info('Títulos que aparecem nas vagas sem secção reconhecida:');
            $this->line('(candidatos a entrar na lista de títulos conhecidos)');
            $this->newLine();

            $this->table(
                ['Vezes', 'Linha'],
                array_map(fn ($linha, $vezes) => [$vezes, $linha], array_keys($falhas), $falhas)
            );
        }

        if ($ficheiro = $this->option('exportar')) {
            $this->exportar($ficheiro, $semSeccao);
        }

        return self::SUCCESS;
    }

    /**
     * Linhas curtas que parecem um título de secção — em maiúsculas, ou a acabar
     * em dois pontos — dentro de anúncios onde não reconhecemos secção nenhuma.
     * É aqui que estão os títulos que ainda não sabemos ler.
     *
     * @return array<int, string>
     */
    private function candidatosATitulo(?string $description): array
    {
        $texto = PlainText::fromHtml($description);
        $candidatos = [];

        foreach (preg_split('/\R/u', $texto) as $linha) {
            $linha = trim(preg_replace('/\s+/u', ' ', $linha));
            $comprimento = mb_strlen($linha, 'UTF-8');

            if ($comprimento < 4 || $comprimento > 45) {
                continue;
            }

            $terminaEmDoisPontos = str_ends_with($linha, ':');
            $tudoMaiusculas = $linha === mb_strtoupper($linha, 'UTF-8') && preg_match('/\p{L}/u', $linha);

            if (!$terminaEmDoisPontos && !$tudoMaiusculas) {
                continue;
            }

            $candidatos[] = mb_strtolower(rtrim($linha, ': '), 'UTF-8');
        }

        return array_unique($candidatos);
    }

    /**
     * @param array<int, Job> $vagas
     */
    private function exportar(string $ficheiro, array $vagas): void
    {
        $dados = array_map(fn (Job $vaga) => [
            'id' => $vaga->id,
            'titulo' => $vaga->title,
            'descricao' => PlainText::fromHtml($vaga->description),
        ], $vagas);

        File::put($ficheiro, json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info(sprintf('Gravadas %d vagas sem secção reconhecida em %s', count($dados), $ficheiro));
    }
}
