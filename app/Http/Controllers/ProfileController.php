<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the user profile form.
     */
    public function show()
    {
        $user = Auth::user();
        $categories = Category::all();
        return view('profile.show', compact('user', 'categories'));
    }

    /**
     * Update the user profile (CV and Categories).
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'cv' => 'nullable|mimes:pdf|max:2048', // Max 2MB PDF
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        // Handle CV Upload
        if ($request->hasFile('cv')) {
            // Delete old CV if exists
            if ($user->cv_path) {
                Storage::disk('public')->delete($user->cv_path);
            }
            // Store new CV
            $path = $request->file('cv')->store('cvs', 'public');
            $user->update(['cv_path' => $path]);
        }

        // Handle Categories Sync
        if ($request->has('categories')) {
            $user->categories()->sync($request->categories);
        } else {
            // If no categories sent (unchecked all), detach all
            $user->categories()->detach();
        }

        return redirect()->back()->with('success', 'Perfil atualizado com sucesso!');
    }

    /**
     * Show jobs matching user's categories.
     */
    public function potentialJobs()
    {
        $user = Auth::user();
        $userCategoryIds = $user->categories->pluck('id');

        // Fetch jobs that belong to any of the user's categories
        $jobs = Job::whereHas('categories', function ($query) use ($userCategoryIds) {
            $query->whereIn('categories.id', $userCategoryIds);
        })
        ->with('categories')
        ->latest()
        ->paginate(12);

        return view('jobs.potential', compact('jobs'));
    }
}
