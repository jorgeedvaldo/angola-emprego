<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function store(Request $request)
    {
        $job = Job::create($request->all());
        return response()->json($job);
    }

    public function getById($id)
    {
        $data = Job::with('categories')->where('id', $id)->get();
        $license = 'This API was developed by Edivaldo Jorge (https://github.com/jorgeedvaldo)';
        $message = 'Operation performed successfully.';
        return response()->json(
            [
                'message' => $message,
                'data' => $data,
                'license' => $license
            ]);
    }
}
