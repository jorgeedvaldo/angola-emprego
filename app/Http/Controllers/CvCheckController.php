<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\Cv\CvEngine;
use App\Services\Cv\JevClient;
use App\Services\CvAnalysisService;
use App\Support\PlainText;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * "Verifique o seu CV": o candidato mede-se contra uma vaga.
 *
 * É a outra ponta do analisador. Lá é o recrutador que traz muitos CVs e uma
 * vaga que escreveu; aqui é o candidato que traz um CV e a vaga é a que está
 * na página — vem da base de dados, não do browser, por isso ninguém pode
 * mandar uma descrição à medida para inflacionar a percentagem.
 *
 * Como o analisador, não guarda nada: o PDF vive no directório temporário
 * durante o pedido e desaparece com ele.
 */
class CvCheckController extends Controller
{
    /** O mesmo tecto do analisador: 5 MB. */
    public const MAX_CV_SIZE_KB = 5120;

    public function check(Request $request, string $slug, CvAnalysisService $analysis)
    {
        // Um CV digitalizado leva o seu tempo no OCR, e o limite por omissão do
        // alojamento partilhado cortaria o pedido a meio.
        set_time_limit(120);

        if (!CvEngine::jevActivo()) {
            return response()->json([
                'ok' => false,
                'message' => __('site.verificar.indisponivel'),
            ], 503);
        }

        $request->validate([
            'cv' => ['required', 'file', 'max:' . self::MAX_CV_SIZE_KB, $this->pdfRule()],
        ], [
            'cv.required' => __('site.verificar.escolha_cv'),
            'cv.max' => __('site.verificar.cv_grande'),
        ]);

        $vaga = Job::publiclyVisible()->where('slug', $slug)->firstOrFail();

        /** @var UploadedFile $ficheiro */
        $ficheiro = $request->file('cv');
        $conteudo = (string) file_get_contents($ficheiro->getRealPath());

        if ($conteudo === '') {
            return response()->json([
                'ok' => false,
                'message' => __('site.verificar.ficheiro_vazio'),
            ], 422);
        }

        $lido = $analysis->analyzeCvContents($conteudo, $ficheiro->getClientOriginalName());

        if (!$lido || trim((string) $lido['text']) === '') {
            return response()->json([
                'ok' => false,
                'message' => __('site.verificar.nao_leu'),
            ], 502);
        }

        // A descrição da vaga é HTML; o JEV recebe texto, com a forma que o
        // autor lhe deu — é o mesmo tratamento que o resto do site faz.
        $descricao = PlainText::fromHtml($vaga->description);
        $pontuacao = (new JevClient())->compatibilidade(
            $vaga->title . "\n\n" . $descricao,
            (string) $lido['text']
        );

        if ($pontuacao === null) {
            return response()->json([
                'ok' => false,
                'message' => __('site.verificar.falhou'),
            ], 502);
        }

        $percentagem = (int) round($pontuacao * 100);

        return response()->json([
            'ok' => true,
            'percentagem' => $percentagem,
            'nivel' => $this->nivel($percentagem),
            'leitura' => __('site.verificar.leitura_' . $this->nivel($percentagem)),
        ]);
    }

    /**
     * Em que faixa cai a percentagem.
     *
     * Serve para dar ao candidato uma frase em vez de um número solto. Os
     * cortes são nossos, não do modelo: o JEV devolve uma probabilidade
     * contínua e não sabe nada destas três gavetas.
     */
    private function nivel(int $percentagem): string
    {
        if ($percentagem >= 70) {
            return 'alta';
        }

        return $percentagem >= 40 ? 'media' : 'baixa';
    }

    private function pdfRule(): \Closure
    {
        return function ($atributo, $valor, $falhar) {
            if (!$valor instanceof UploadedFile) {
                $falhar(__('site.verificar.so_pdf'));

                return;
            }

            if (strtolower($valor->getClientOriginalExtension()) !== 'pdf') {
                $falhar(__('site.verificar.so_pdf'));
            }
        };
    }
}
