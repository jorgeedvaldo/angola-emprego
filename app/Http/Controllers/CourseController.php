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

    public function certificate($slug)
    {
        $course = \App\Models\Course::where('slug', $slug)->firstOrFail();
        
        // Check if all lessons completed
        $totalLessons = $course->lessons()->count();
        $completedLessons = auth()->user()->lessons()
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->whereNotNull('completed_at')
            ->count();

        if ($completedLessons < $totalLessons || $totalLessons === 0) {
            return back()->with('error', 'Você precisa concluir todas as aulas para baixar o certificado.');
        }

        // Generate PDF
        try {
             $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('courses.certificate', compact('course'));
             return $pdf->download('certificado-' . $course->slug . '.pdf');
        } catch (\Exception $e) {
            // Fallback if package logic fails or not installed
             return back()->with('error', 'Erro ao gerar certificado. Contacte o suporte.');
        }
    }
}
