<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Job;
use Illuminate\Http\Request;

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
