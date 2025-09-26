<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $jobs = Job::orderByRaw('id DESC')->paginate(10);
        $posts = Post::orderBy('id', 'desc')->paginate(6);
        $categories = Category::orderBy('name')->get();
        return view('home', compact('posts','jobs', 'categories'));
    }

    public function siteMapGenerator()
    {
        $jobs = Job::orderByRaw('id DESC')->paginate(500);
		$posts = Post::orderByRaw('id DESC')->paginate(300);
        return response()->view('xml.sitemap', compact('jobs', 'posts'))->header('Content-Type', 'text/xml');
    }

	public function feedGenerator()
    {
        //$jobs = Job::orderByRaw('id DESC')->paginate(10);
        $posts = Post::orderBy('id', 'desc')->paginate(10);
        return response()->view('xml.feed', compact('posts'))->header('Content-Type', 'text/xml');
    }
}
