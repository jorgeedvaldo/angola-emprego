<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobApplicationController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $job = Job::where('slug', $slug)->firstOrFail();

        if (!$job->acceptsOnlineApplications()) {
            return back()->with('error', 'Esta vaga não aceita candidaturas por esta página.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ], [
            'attachment.required' => 'Anexe o seu CV.',
            'attachment.mimes' => 'O CV deve ser um ficheiro PDF, DOC ou DOCX.',
            'attachment.max' => 'O CV não pode ultrapassar 5 MB.',
        ]);

        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyApplied) {
            return back()->withInput()->with('error', 'Já existe uma candidatura com este email para esta vaga.');
        }

        $file = $request->file('attachment');
        $path = $file->store('job-applications/' . $job->id, 'local');

        JobApplication::create([
            'job_id' => $job->id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'attachment_path' => $path,
            'attachment_name' => $file->getClientOriginalName(),
            'status' => 'new',
        ]);

        return back()->with('success', 'Candidatura enviada com sucesso. A empresa receberá o seu CV.');
    }
}
