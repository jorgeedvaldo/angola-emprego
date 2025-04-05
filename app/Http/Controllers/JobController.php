<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        $jobs = Job::orderByRaw('id DESC')->paginate(30);
        //$categories = Category::orderBy('name')->get();
        return view('jobs', compact('jobs'));
    }

    public function getBySlug($slug)
    {
        try
        {
            $job = Job::with('categories')->where('slug', $slug)->get()[0];

            $categories = Category::orderBy('name')->get();

            $LastJobs = Job::where('slug', '<>', $slug)->orderByRaw('id DESC')->get();

            return view('job-detail', compact('job', 'categories', 'LastJobs'));
        }
        catch(Exception $ex)
        {
            abort(404);
        }
    }
}
