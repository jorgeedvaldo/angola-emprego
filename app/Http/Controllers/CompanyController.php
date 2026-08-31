<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobApplicationAttachment;
use App\Support\HtmlSanitizer;
use App\Support\VectorSimilarity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::where('approval_status', 'approved')
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->withCount('jobs')
            ->orderBy('name')
            ->paginate(18);

        return view('companies.index', compact('companies'));
    }

    public function show(string $slug)
    {
        $company = Company::where('slug', $slug)
            ->where('approval_status', 'approved')
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->firstOrFail();
        $jobs = $company->jobs()->orderByDesc('id')->paginate(10);

        return view('companies.show', compact('company', 'jobs'));
    }

    public function showJob(string $slug, string $jobSlug)
    {
        $company = Company::where('slug', $slug)
            ->where('approval_status', 'approved')
            ->whereHas('user', fn ($query) => $query->whereNotNull('email_verified_at'))
            ->firstOrFail();
        $job = $company->jobs()->where('slug', $jobSlug)->firstOrFail();

        return view('companies.job', compact('company', 'job'));
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
            'headline' => 'nullable|string|max:180',
            'description' => 'nullable|string|max:5000',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'linkedin_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'max_attachments' => 'required|integer|min:1|max:' . Company::MAX_ATTACHMENTS_LIMIT,
            'theme_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'slug.regex' => 'O URL da página deve conter apenas letras minúsculas, números e hífens.',
            'slug.unique' => 'Este URL já está a ser usado por outra empresa.',
            'theme_color.regex' => 'Seleccione uma cor válida.',
            'cover_image.max' => 'A foto de capa não pode ultrapassar 5 MB.',
        ]);

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('images/companies', 'public');
        } else {
            unset($validated['logo']);
        }

        if ($request->hasFile('cover_image')) {
            if ($company->cover_image) {
                Storage::disk('public')->delete($company->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('images/companies/covers', 'public');
        } else {
            unset($validated['cover_image']);
        }

        if (!empty($validated['theme_color'])) {
            $validated['theme_color'] = strtoupper($validated['theme_color']);
        } else {
            unset($validated['theme_color']);
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
            'description' => HtmlSanitizer::clean($validated['description']),
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

        $cleanDescription = HtmlSanitizer::clean($validated['description']);

        $job->update([
            'title' => $validated['title'],
            'location' => $validated['location'],
            'description' => $cleanDescription,
            'email_or_link' => $validated['email_or_link'] ?? $job->email_or_link,
            'company' => Auth::user()->company->name,
            'description_vector' => $cleanDescription === $job->getOriginal('description') ? $job->description_vector : null,
            'description_vector_generated_at' => $cleanDescription === $job->getOriginal('description') ? $job->description_vector_generated_at : null,
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

        $applications = $job->applications()->with('files')->orderByDesc('id')->get()
            ->map(function (JobApplication $application) use ($job) {
                $application->match_score = ($job->description_vector && $application->cv_vector)
                    ? VectorSimilarity::cosine($job->description_vector, $application->cv_vector)
                    : null;

                return $application;
            })
            ->sortByDesc(fn (JobApplication $application) => $application->match_score ?? -2)
            ->values();

        $jobDescriptionText = trim(strip_tags($job->description));

        return view('companies.jobs.applications', compact('company', 'job', 'applications', 'jobDescriptionText'));
    }

    public function storeJobVector(Request $request, Job $job)
    {
        $this->authorizeJob($job);

        $validated = $request->validate([
            'vector' => ['required', 'array', 'size:' . VectorSimilarity::DIMENSIONS],
            'vector.*' => 'numeric',
        ]);

        $job->update([
            'description_vector' => $validated['vector'],
            'description_vector_generated_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function storeApplicationVector(Request $request, JobApplication $application)
    {
        $this->authorizeJob($application->job);

        $validated = $request->validate([
            'text' => ['required', 'string', 'max:20000'],
            'vector' => ['required', 'array', 'size:' . VectorSimilarity::DIMENSIONS],
            'vector.*' => 'numeric',
        ]);

        $application->update([
            'cv_text' => $validated['text'],
            'cv_vector' => $validated['vector'],
            'cv_analyzed_at' => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function downloadApplicationAttachment(JobApplication $application)
    {
        $job = $application->job;
        $this->authorizeJob($job);

        $file = $application->files()->first();
        $path = $file?->path ?? $application->attachment_path;
        $downloadName = $file?->original_name ?: ($application->attachment_name ?: basename((string) $path));

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->download($path, $downloadName);
    }

    public function downloadAttachment(JobApplicationAttachment $attachment)
    {
        $job = $attachment->application->job;
        $this->authorizeJob($job);

        if (!Storage::disk('local')->exists($attachment->path)) {
            abort(404);
        }

        $downloadName = $attachment->original_name ?: basename($attachment->path);

        return Storage::disk('local')->download($attachment->path, $downloadName);
    }

    private function authorizeJob(Job $job): void
    {
        if ((int) $job->company_id !== (int) Auth::user()->company->id) {
            abort(403);
        }
    }
}
