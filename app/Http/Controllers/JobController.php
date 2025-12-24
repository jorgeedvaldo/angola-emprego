<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

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

        $jobs = $query->orderByRaw('id DESC')->paginate(15);
        $jobs->appends($request->all());

        // Sidebar Data
        $categories = Category::withCount('jobs')->orderBy('name')->get();
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
            $job = Job::with('categories')->where('slug', $slug)->get()[0];

            $categories = Category::orderBy('name')->get();

            $LastJobs = Job::where('slug', '<>', $slug)->orderByRaw('id DESC')->paginate(8);

            return view('job-detail', compact('job', 'categories', 'LastJobs'));
        }
        catch(Exception $ex)
        {
            abort(404);
        }
    }
}
