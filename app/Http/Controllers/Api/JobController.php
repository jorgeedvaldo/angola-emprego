<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function store(Request $request)
    {
        $pais = $this->resolverPais($request);

        // O país é a única coisa que se valida aqui: quem publica por esta rota
        // já vinha a mandar vagas antes de existirem países, e recusar-lhe um
        // campo novo partiria a importação que está a correr hoje.
        if ($pais === false) {
            return response()->json([
                'message' => 'País desconhecido. Use o código ISO de dois dígitos (AO, BR, ES...) ou o nome do país.',
                'country' => $request->input('country', $request->input('country_code')),
            ], 422);
        }

        $job = Job::create($request->all() + ['country_id' => $pais->id]);

        return response()->json($job->load('country'));
    }

    /**
     * Apaga uma vaga e tudo o que lhe está agarrado.
     *
     * Não é só um $job->delete(). O category_jobs tem chave estrangeira sem
     * cascata, por isso uma vaga com categorias recusa-se a ser apagada em
     * MySQL; e as candidaturas, que essas descem em cascata, deixam para trás
     * os ficheiros no disco, que ninguém mais vai lá buscar.
     *
     * Uma vaga com candidaturas não se apaga por engano: são CVs que pessoas
     * enviaram e que desaparecem com ela. É preciso pedir "force" de propósito.
     */
    public function destroy(Request $request, $id)
    {
        $job = Job::with('categories')->find($id);

        if (!$job) {
            return response()->json(['message' => 'Vaga não encontrada.'], 404);
        }

        $candidaturas = $job->applications()->count();
        $forcado = filter_var($request->input('force', false), FILTER_VALIDATE_BOOLEAN);

        if ($candidaturas > 0 && !$forcado) {
            return response()->json([
                'message' => "Esta vaga tem $candidaturas candidatura(s), que são apagadas com ela. "
                    . 'Repita com \'force\': true para confirmar.',
                'job_id' => $job->id,
                'applications' => $candidaturas,
            ], 409);
        }

        $apagado = [
            'id' => $job->id,
            'title' => $job->title,
            'slug' => $job->slug,
            'applications' => $candidaturas,
        ];

        // Os caminhos são recolhidos antes: depois de a linha desaparecer já
        // não há por onde saber que ficheiros eram dela.
        $imagem = $job->image;

        DB::transaction(function () use ($job) {
            // Sem cascata no category_jobs: se ficarem, o delete rebenta.
            $job->categories()->detach();

            if (Schema::hasTable('social_media_jobs')) {
                DB::table('social_media_jobs')->where('job_id', $job->id)->delete();
            }

            // As candidaturas e os seus anexos descem em cascata pela base de
            // dados; aqui só desaparece a vaga.
            $job->delete();
        });

        $ficheiros = $this->limparFicheiros($apagado['id'], $imagem);

        return response()->json([
            'message' => 'Vaga apagada.',
            'deleted' => $apagado + ['files_removed' => $ficheiros],
        ]);
    }

    /**
     * Tira do disco o que a base de dados não sabe apagar: a capa da vaga, a
     * miniatura que saiu dela, e a pasta dos CVs que os candidatos enviaram.
     *
     * @return int quantos ficheiros e pastas foram removidos
     */
    private function limparFicheiros(int $jobId, ?string $imagem): int
    {
        $removidos = 0;

        if ($imagem) {
            foreach ([$imagem, 'thumb/' . $imagem] as $caminho) {
                if (Storage::disk('public')->exists($caminho) && Storage::disk('public')->delete($caminho)) {
                    $removidos++;
                }
            }
        }

        $pastaCandidaturas = 'job-applications/' . $jobId;

        if (Storage::disk('local')->exists($pastaCandidaturas)) {
            Storage::disk('local')->deleteDirectory($pastaCandidaturas);
            $removidos++;
        }

        return $removidos;
    }

    public function getById($id)
    {
        $data = Job::with(['categories', 'country'])->where('id', $id)->get();
        $license = 'This API was developed by Edivaldo Jorge (https://github.com/jorgeedvaldo)';
        $message = 'Operation performed successfully.';
        return response()->json(
            [
                'message' => $message,
                'data' => $data,
                'license' => $license
            ]);
    }

    /**
     * Descobre o país da vaga a partir do que vier no pedido.
     *
     * Aceita-se o id (country_id), o código ISO ou o nome, em português ou em
     * inglês — quem publica de fora manda o que tem à mão. Sem nada disso a vaga
     * é de Angola, que era o que todas eram até agora.
     *
     * @return Country|false  false quando veio um país que não reconhecemos
     */
    private function resolverPais(Request $request)
    {
        if ($id = $request->input('country_id')) {
            $pais = Country::find($id);

            return $pais ?: false;
        }

        $indicado = $request->input('country', $request->input('country_code'));

        if (trim((string) $indicado) === '') {
            return Country::find(Country::idPorOmissao());
        }

        return Country::porCodigoOuNome($indicado) ?: false;
    }
}
