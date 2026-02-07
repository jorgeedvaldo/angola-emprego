<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $query = Job::query();

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $location = $request->input('location');
            $query->where('location', 'like', "%{$location}%");
        }

        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('categories', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }


        if (!$request->hasAny(['q', 'location', 'category']) && ($request->get('page', 1) == 1)) {
            $cachedJobs = Job::getCachedLatest();
            $perPage = 15;
            $currentPage = 1;
            $currentItems = $cachedJobs->slice(0, $perPage);
            // We use a simple count query for total, or just a large number if we want to avoid it.
            // For accurate pagination links, we need real count.
            $total = Job::count(); 
            
            $jobs = new LengthAwarePaginator(
                $currentItems, 
                $total, 
                $perPage, 
                $currentPage, 
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
             $jobs = $query->orderByRaw('id DESC')->paginate(15);
        }
        
        $jobs->appends($request->all());

        // Sidebar Data
        $categories = Category::getCachedWithJobs();
        // Top 15 companies by job count
        $topCompanies = Job::select('company', \DB::raw('count(*) as total'))
                            ->groupBy('company')
                            ->orderByDesc('total')
                            ->limit(15)
                            ->get();

        return view('jobs', compact('jobs', 'categories', 'topCompanies'));
    }

    public function getBySlug($slug)
    {
        try
        {
            $job = Cache::remember('job_' . $slug, 1440, function () use ($slug) {
                return Job::with('categories')->where('slug', $slug)->firstOrFail();
            });

            $categories = Category::getCachedAll();

            $LastJobs = Job::getCachedLatest()
                        ->reject(function ($value) use ($slug) {
                            return $value->slug === $slug;
                        })
                        ->take(8);

            return view('job-detail', compact('job', 'categories', 'LastJobs'));
        }
        catch(Exception $ex)
        {
            abort(404);
        }
    }
}
