<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('jobs')
            ->orderBy('name')
            ->paginate(18);

        return view('companies.index', compact('companies'));
    }

    public function show(string $slug)
    {
        $company = Company::where('slug', $slug)->firstOrFail();
        $jobs = $company->jobs()->orderByDesc('id')->paginate(10);

        return view('companies.show', compact('company', 'jobs'));
    }

    public function dashboard()
    {
        $company = Auth::user()->company;
        $jobs = $company->jobs()->withCount('applications')->orderByDesc('id')->get();
        $applicationsCount = $jobs->sum('applications_count');

        return view('companies.dashboard', compact('company', 'jobs', 'applicationsCount'));
    }

    public function update(Request $request)
    {
        $company = Auth::user()->company;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => [
                'required',
                'string',
                'max:80',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:companies,slug,' . $company->id,
            ],
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], [
            'slug.regex' => 'O URL da página deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Este URL já está a ser usado por outra empresa.',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('images/companies', 'public');
        } else {
            unset($validated['logo']);
        }

        $company->update($validated);

        $company->jobs()->update(['company' => $company->name]);

        return back()->with('success', 'Página da empresa actualizada.');
    }

    public function createJob()
    {
        $company = Auth::user()->company;
        $categories = Category::orderBy('name')->get();

        return view('companies.jobs.create', compact('company', 'categories'));
    }

    public function storeJob(Request $request)
    {
        $company = Auth::user()->company;

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'email_or_link' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $job = Job::create([
            'title' => $validated['title'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'email_or_link' => $validated['email_or_link'] ?? $company->email ?? Auth::user()->email,
            'company' => $company->name,
            'company_id' => $company->id,
            'image' => $company->logo,
        ]);

        if (!empty($validated['categories'])) {
            $job->categories()->sync($validated['categories']);
        }

        return redirect()->route('company.dashboard')->with('success', 'Vaga publicada com sucesso.');
    }

    public function editJob(Job $job)
    {
        $this->authorizeJob($job);
        $company = Auth::user()->company;
        $categories = Category::orderBy('name')->get();
        $selectedCategories = $job->categories()->pluck('categories.id')->all();

        return view('companies.jobs.edit', compact('company', 'job', 'categories', 'selectedCategories'));
    }

    public function updateJob(Request $request, Job $job)
    {
        $this->authorizeJob($job);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'required|string',
            'email_or_link' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $job->update([
            'title' => $validated['title'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'email_or_link' => $validated['email_or_link'] ?? $job->email_or_link,
            'company' => Auth::user()->company->name,
        ]);

        $job->categories()->sync($validated['categories'] ?? []);

        return redirect()->route('company.dashboard')->with('success', 'Vaga actualizada.');
    }

    public function destroyJob(Job $job)
    {
        $this->authorizeJob($job);
        $job->delete();

        return redirect()->route('company.dashboard')->with('success', 'Vaga removida.');
    }

    public function applications(Job $job)
    {
        $this->authorizeJob($job);
        $company = Auth::user()->company;
        $applications = $job->applications()->orderByDesc('id')->paginate(20);

        return view('companies.jobs.applications', compact('company', 'job', 'applications'));
    }

    public function downloadApplicationAttachment(\App\Models\JobApplication $application)
    {
        $job = $application->job;
        $this->authorizeJob($job);

        if (!Storage::disk('local')->exists($application->attachment_path)) {
            abort(404);
        }

        $downloadName = $application->attachment_name ?: basename($application->attachment_path);

        return Storage::disk('local')->download($application->attachment_path, $downloadName);
    }

    private function authorizeJob(Job $job): void
    {
        if ($job->company_id !== Auth::user()->company->id) {
            abort(403);
        }
    }
}
