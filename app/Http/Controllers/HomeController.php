<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Support\Resiliente;

class HomeController extends Controller
{
    public function index()
    {
        $jobs = Job::getCachedLatest()->take(12);
        $posts = Post::getCachedLatest()->take(9);
        $categories = Category::getCachedAll();
        return view('home', compact('posts','jobs', 'categories'));
    }

    public function siteMapGenerator()
    {
        $staticPages = [
            '/', '/sobre', '/vagas', '/noticias', '/cursos', '/empresas', '/empresas-e-recrutadores', '/analisador-de-cv',
            '/noticias/resultados-concurso-csmj-2026'
        ];

        // O sitemap é a página mais cara do site: varre as vagas e os artigos
        // todos. Quem a pede são os robôs, que não precisam dela ao minuto.
        [$jobs, $posts, $courses, $companies] = Resiliente::lembrar(
            'sitemap',
            config('resiliencia.sitemap'),
            fn () => [
                Job::publiclyVisible()->orderByRaw('id DESC')->paginate(12500),
                Post::orderByRaw('id DESC')->paginate(12500),
                \App\Models\Course::orderBy('id', 'desc')->get(),
                \App\Models\Company::orderBy('name')->get(),
            ]
        );

        $categories = Category::getCachedAll();

        return response()->view('xml.sitemap', compact('jobs', 'posts', 'categories', 'staticPages', 'courses', 'companies'))
            ->header('Content-Type', 'text/xml');
    }

	public function feedGenerator()
    {
        //$jobs = Job::orderByRaw('id DESC')->paginate(10);
        $posts = Resiliente::lembrar(
            'feed_geral',
            config('resiliencia.normal'),
            fn () => Post::orderBy('id', 'desc')->paginate(10)
        );
        return response()->view('xml.feed', compact('posts'))->header('Content-Type', 'text/xml');
    }
}
