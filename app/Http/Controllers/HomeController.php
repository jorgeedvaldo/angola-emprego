<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function index()
    {
        $jobs = Job::getCachedLatest()->take(12);
        $posts = Post::getCachedLatest()->take(9);
        $categories = Category::orderBy('name')->get();
        return view('home', compact('posts','jobs', 'categories'));
    }

    public function siteMapGenerator()
    {
        $staticPages = [
            '/', '/sobre', '/vagas', '/noticias', '/cursos'
        ];

        $jobs = Job::orderByRaw('id DESC')->paginate(12500);
		$posts = Post::orderByRaw('id DESC')->paginate(12500);
        $categories = Category::orderBy('name')->get();

        return response()->view('xml.sitemap', compact('jobs', 'posts', 'categories', 'staticPages'))
            ->header('Content-Type', 'text/xml');
    }

	public function feedGenerator()
    {
        //$jobs = Job::orderByRaw('id DESC')->paginate(10);
        $posts = Post::orderBy('id', 'desc')->paginate(10);
        return response()->view('xml.feed', compact('posts'))->header('Content-Type', 'text/xml');
    }
}
