<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = \App\Models\Course::where('is_published', true)->orderBy('created_at', 'desc')->get();
        return view('courses.index', compact('courses'));
    }

    public function show($slug)
    {
        $course = \App\Models\Course::where('slug', $slug)
            ->where('is_published', true)
            ->with(['lessons' => function ($query) {
                $query->orderBy('sort_order');
            }])
            ->firstOrFail();

        return view('courses.show', compact('course'));
    }

    public function attend($slug, $lessonSlug)
    {
        $course = \App\Models\Course::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $lesson = $course->lessons()->where('slug', $lessonSlug)->firstOrFail();
        
        // Track progress if not tracked
        if (!auth()->user()->lessons()->where('lesson_id', $lesson->id)->exists()) {
             auth()->user()->lessons()->attach($lesson->id, ['completed_at' => null]);
        }
        
        $previous = $course->lessons()->where('sort_order', '<', $lesson->sort_order)->orderBy('sort_order', 'desc')->first();
        $next = $course->lessons()->where('sort_order', '>', $lesson->sort_order)->orderBy('sort_order')->first();

        return view('courses.attend', compact('course', 'lesson', 'previous', 'next'));
    }

    public function completeLesson(Request $request, $slug, $lessonSlug)
    {
        $course = \App\Models\Course::where('slug', $slug)->firstOrFail();
        $lesson = $course->lessons()->where('slug', $lessonSlug)->firstOrFail();

        auth()->user()->lessons()->syncWithoutDetaching([$lesson->id => ['completed_at' => now()]]);

        $next = $course->lessons()->where('sort_order', '>', $lesson->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            return redirect()->route('courses.attend', ['slug' => $course->slug, 'lessonSlug' => $next->slug])->with('success', 'Aula concluída!');
        }

        return redirect()->route('courses.show', ['slug' => $course->slug])->with('success', 'Curso concluído!');
    }

    public function previewCertificate($slug)
    {
        $course = \App\Models\Course::where('slug', $slug)->firstOrFail();
        
        $this->checkCompletion($course);

        return view('courses.certificate_preview', compact('course'));
    }

    public function downloadCertificate($slug)
    {
        $course = \App\Models\Course::where('slug', $slug)->firstOrFail();
        
        $this->checkCompletion($course);

        // Generate PDF
        //try {
             $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('courses.certificate', compact('course'))
                    ->setPaper('a4', 'landscape');
             return $pdf->download('certificado-' . $course->slug . '.pdf');
        //} catch (\Exception $e) {
          //   return back()->with('error', 'Erro ao gerar certificado. Contacte o suporte: ' . $e->getMessage());
       // }
    }

    public function verifyCertificate(\App\Models\User $user, $courseSlug)
    {
        $course = \App\Models\Course::where('slug', $courseSlug)->firstOrFail();
        
        // Check if user completed the course
        // Re-using logic from User model manually or using the attribute if accessible (but attribute filters all courses)
        // More efficient to check specific course:
        
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) abort(404, 'Curso sem aulas.');

        $completedLessons = $user->lessons()
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();
            
        if ($completedLessons < $totalLessons) {
            abort(404, 'Certificado não encontrado ou curso não concluído.');
        }

        // Get completion date
        $lastLesson = $user->lessons()
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('lesson_user.completed_at')
            ->orderBy('lesson_user.completed_at', 'desc')
            ->first();
            
        $completionDate = $lastLesson ? \Carbon\Carbon::parse($lastLesson->pivot->completed_at) : now();

        return view('courses.verification', compact('user', 'course', 'completionDate'));
    }

    private function checkCompletion($course)
    {
        $totalLessons = $course->lessons()->count();
        $completedLessons = auth()->user()->lessons()
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();

        if ($completedLessons < $totalLessons || $totalLessons === 0) {
            abort(403, 'Você precisa concluir todas as aulas para acessar o certificado.');
        }
    }
}
