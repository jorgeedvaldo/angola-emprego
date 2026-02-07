<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function index()
    {
        if (request()->get('page', 1) == 1) {
            $cachedPosts = Post::getCachedLatest();
            $perPage = 30;
            $currentPage = 1;
            $currentItems = $cachedPosts->slice(0, $perPage);
            $total = Post::count(); 
            
            $posts = new LengthAwarePaginator(
                $currentItems, 
                $total, 
                $perPage, 
                $currentPage, 
                ['path' => request()->url(), 'query' => request()->query()]
            );
        } else {
            $posts = Post::orderByRaw('id DESC')->paginate(30);
        }
        $categories = Category::orderBy('name')->get();
        return view('blog', compact('posts', 'categories'));
    }

    public function getBySlug($slug)
    {
        try
        {
            $post = Cache::remember('post_' . $slug, 1440, function () use ($slug) {
                return Post::where('slug', $slug)->firstOrFail();
            });

            $categories = Category::orderBy('name')->get();

            $LastPosts = Post::getCachedLatest()
                        ->reject(function ($value) use ($slug) {
                            return $value->slug === $slug;
                        })
                        ->take(8);

            return view('post', compact('post', 'categories', 'LastPosts'));
        }
        catch(Exception $ex)
        {
            abort(404);
        }
    }
}
