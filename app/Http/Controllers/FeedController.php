<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Um feed RSS por artigo e por vaga, no endereço da própria página com /feed
 * ao fim — a forma a que os leitores de feeds já estão habituados do WordPress.
 *
 * O feed do WordPress nesse endereço é dos comentários do artigo. Aqui não há
 * comentários, e um canal permanentemente vazio não serve a ninguém: o nosso
 * leva o próprio artigo (ou a própria vaga) e o que vem a seguir, para que quem
 * subscreve a partir de uma página continue a receber alguma coisa.
 */
class FeedController extends Controller
{
    /** Quantos vizinhos acompanham o item pedido. */
    private const VIZINHOS = 10;

    public function post(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $outros = Post::where('id', '!=', $post->id)
            ->orderByDesc('id')
            ->limit(self::VIZINHOS)
            ->get();

        return $this->canal(
            $post->title,
            url('/noticias/' . $post->slug),
            url('/noticias/' . $post->slug . '/feed'),
            'Artigos do Angola Emprego, a começar em "' . $post->title . '".',
            $this->artigos($outros->prepend($post))
        );
    }

    public function job(string $slug)
    {
        $job = Job::publiclyVisible()->with('country')->where('slug', $slug)->firstOrFail();

        // De uma vaga de empresa seguem-se as outras vagas dessa empresa: é o
        // que torna este feed útil a quem quer acompanhar quem contrata. Das
        // vagas sem empresa registada seguem-se as mais recentes do site.
        $outras = Job::publiclyVisible()
            ->with('country')
            ->where('id', '!=', $job->id)
            ->when($job->company_id, fn ($consulta) => $consulta->where('company_id', $job->company_id))
            ->orderByDesc('id')
            ->limit(self::VIZINHOS)
            ->get();

        return $this->canal(
            $job->title,
            url('/vagas/' . $job->slug),
            url('/vagas/' . $job->slug . '/feed'),
            $job->company_id
                ? 'Vagas de ' . $job->company . ' no Angola Emprego.'
                : 'Vagas do Angola Emprego, a começar em "' . $job->title . '".',
            $this->vagas($outras->prepend($job))
        );
    }

    // ------------------------------------------------------------- o canal

    private function canal(string $titulo, string $ligacao, string $proprio, string $descricao, array $itens)
    {
        $construido = collect($itens)->max('data') ?: now();

        return response()
            ->view('xml.feed-canal', compact('titulo', 'ligacao', 'proprio', 'descricao', 'itens', 'construido'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    // -------------------------------------------------------------- itens

    private function artigos(Collection $posts): array
    {
        return $posts->map(fn (Post $post) => [
            'titulo' => $post->title,
            'ligacao' => url('/noticias/' . $post->slug),
            'data' => $post->updated_at ?: $post->created_at,
            'resumo' => $this->semCdata(Str::limit(strip_tags($post->description), 400)),
            'conteudo' => $this->semCdata($post->description),
            'imagem' => $post->image ? asset('storage/' . $post->image) : null,
            'categorias' => [],
        ])->all();
    }

    private function vagas(Collection $jobs): array
    {
        return $jobs->map(fn (Job $job) => [
            'titulo' => $job->title,
            'ligacao' => url('/vagas/' . $job->slug),
            'data' => $job->updated_at ?: $job->created_at,
            'resumo' => $this->semCdata(
                trim($job->company . ' — ' . $job->location) . '. '
                . Str::limit(strip_tags($job->description), 400)
            ),
            'conteudo' => $this->semCdata($job->description),
            'imagem' => $job->image ? asset('storage/' . $job->image) : null,
            'categorias' => array_map(
                fn ($nome) => $this->semCdata($nome),
                array_values(array_filter([$job->company, $job->location, optional($job->country)->name]))
            ),
        ])->all();
    }

    /**
     * O conteúdo vai dentro de um CDATA, que um "]]>" no meio do texto fecharia
     * a meio e partia o XML todo.
     */
    private function semCdata(?string $texto): string
    {
        return str_replace(']]>', ']]&gt;', (string) $texto);
    }
}
