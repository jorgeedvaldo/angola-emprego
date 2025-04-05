<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::orderByRaw('id DESC')->paginate(30);
        $categories = Category::orderBy('name')->get();
        return view('posts', compact('posts', 'categories'));
    }

    public function getBySlug($slug)
    {
        try
        {
            $post = Post::where('slug', $slug)->get()[0];

            $categories = Category::orderBy('name')->get();

            $LastPosts = Post::where('slug', '<>', $slug)->orderByRaw('id DESC')->get();

            return view('post', compact('post', 'categories', 'LastPosts'));
        }
        catch(Exception $ex)
        {
            abort(404);
        }
    }
}
