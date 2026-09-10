<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CvAnalysisService
{
    public function embed(string $text): ?array
    {
        if (!$this->configured()) {
            Log::warning('CvAnalysisService: analisecv não configurado (ANALISECV_URL/ANALISECV_API_KEY em falta).');

            return null;
        }

        try {
            $response = $this->client()->post('/embed', ['text' => $text]);
        } catch (\Throwable $exception) {
            Log::error('CvAnalysisService: falha ao contactar /embed.', ['message' => $exception->getMessage()]);

            return null;
        }

        if (!$response->successful() || $response->json('ok') !== true) {
            Log::error('CvAnalysisService: /embed rejeitou o pedido.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        return [
            'vector' => $response->json('vector'),
            'model' => $response->json('model'),
        ];
    }

    /**
     * Analisa um CV já guardado em disco (usado pela análise de candidaturas das
     * empresas, onde o anexo fica arquivado).
     */
    public function analyzeCv(string $storageDisk, string $path): ?array
    {
        if (!$this->configured()) {
            Log::warning('CvAnalysisService: analisecv não configurado (ANALISECV_URL/ANALISECV_API_KEY em falta).');

            return null;
        }

        if (!Storage::disk($storageDisk)->exists($path)) {
            return null;
        }

        return $this->analyzeCvContents(Storage::disk($storageDisk)->get($path), basename($path));
    }

    /**
     * Analisa o conteúdo de um CV sem o guardar em lado nenhum — é o que o
     * analisador público usa: o ficheiro chega no pedido, é reencaminhado para o
     * serviço de análise e desaparece com o fim do pedido.
     */
    public function analyzeCvContents(string $contents, string $filename): ?array
    {
        if (!$this->configured()) {
            Log::warning('CvAnalysisService: analisecv não configurado (ANALISECV_URL/ANALISECV_API_KEY em falta).');

            return null;
        }

        try {
            $response = $this->client()
                ->attach('cv', $contents, $filename)
                ->post('/analyze-cv');
        } catch (\Throwable $exception) {
            Log::error('CvAnalysisService: falha ao contactar /analyze-cv.', ['message' => $exception->getMessage()]);

            return null;
        }

        if (!$response->successful() || $response->json('ok') !== true) {
            Log::error('CvAnalysisService: /analyze-cv rejeitou o pedido.', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return null;
        }

        return [
            'text' => $response->json('text'),
            'vector' => $response->json('vector'),
            'model' => $response->json('model'),
        ];
    }

    private function configured(): bool
    {
        return (bool) config('services.analisecv.url') && (bool) config('services.analisecv.api_key');
    }

    private function client()
    {
        return Http::withHeaders(['X-API-Key' => config('services.analisecv.api_key')])
            ->baseUrl(rtrim(config('services.analisecv.url'), '/'))
            ->timeout(120);
    }
}
