<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobApplicationAttachment;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $job = Job::publiclyVisible()->with('companyRecord')->where('slug', $slug)->firstOrFail();

        if (!$job->acceptsOnlineApplications() || !$job->companyRecord) {
            return back()->with('error', 'Esta vaga não aceita candidaturas por esta página.');
        }

        $maxFiles = $job->companyRecord->allowedAttachmentCount();

        $uploaded = collect($request->file('attachments', []))
            ->filter(function ($file) {
                return $file instanceof UploadedFile
                    && $file->isValid()
                    && $file->getSize() > 0;
            })
            ->values();

        $request->files->set('attachments', $uploaded->all());

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'attachments' => 'required|array|min:1|max:' . $maxFiles,
            'attachments.*' => [
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    if (!$value instanceof UploadedFile) {
                        $fail('Cada anexo deve ser um ficheiro.');
                        return;
                    }
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'doc', 'docx'], true)) {
                        $fail('Cada anexo deve ser PDF, DOC ou DOCX.');
                    }
                },
            ],
        ], [
            'attachments.required' => 'Anexe pelo menos um ficheiro (CV).',
            'attachments.min' => 'Anexe pelo menos um ficheiro (CV).',
            'attachments.max' => 'Pode anexar no máximo ' . $maxFiles . ' ficheiro(s).',
            'attachments.*.mimes' => 'Cada anexo deve ser PDF, DOC ou DOCX.',
            'attachments.*.max' => 'Cada anexo não pode ultrapassar 5 MB.',
        ]);

        $alreadyApplied = JobApplication::where('job_id', $job->id)
            ->where('email', $validated['email'])
            ->exists();

        if ($alreadyApplied) {
            return back()->withInput()->with('error', 'Já existe uma candidatura com este email para esta vaga.');
        }

        $files = $uploaded;
        $first = $files[0];
        $firstPath = $first->store('job-applications/' . $job->id, 'local');

        $application = JobApplication::create([
            'job_id' => $job->id,
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'attachment_path' => $firstPath,
            'attachment_name' => $first->getClientOriginalName(),
            'status' => 'new',
        ]);

        foreach ($files as $index => $file) {
            $path = $index === 0
                ? $firstPath
                : $file->store('job-applications/' . $job->id, 'local');

            JobApplicationAttachment::create([
                'job_application_id' => $application->id,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        return back()->with('success', 'Candidatura enviada com sucesso. A empresa receberá os seus anexos.');
    }
}
