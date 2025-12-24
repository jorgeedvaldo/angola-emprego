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

        $jobs = $query->orderByRaw('id DESC')->paginate(30);
        
        // Append query parameters to pagination links
        $jobs->appends($request->all());

        return view('jobs', compact('jobs'));
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
