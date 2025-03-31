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
}
