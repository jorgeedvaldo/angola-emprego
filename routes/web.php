<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ToolsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sobre', [AboutController::class, 'index'])->name('sobre');
Route::get('/vagas', [JobController::class, 'index'])->name('vagas');
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/atm-com-dinheiro', [ToolsController::class, 'index']);
Route::get('/sitemap.xml', [HomeController::class, 'siteMapGenerator'])->name('sitemap');
Route::get('/feed', [HomeController::class, 'feedGenerator'])->name('feed');


Route::get('/vagas/{slug}', [JobController::class, 'getBySlug']);

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Profile & Recommendations
    Route::get('/perfil', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/vagas-sugeridas', [App\Http\Controllers\ProfileController::class, 'potentialJobs'])->name('jobs.potential');
    Route::get('/planos', [App\Http\Controllers\ProfileController::class, 'plans'])->name('plans.index');
    Route::get('/planos/confirmar', [App\Http\Controllers\ProfileController::class, 'confirm'])->name('plans.confirm');
    Route::post('/planos/subscrever', [App\Http\Controllers\ProfileController::class, 'subscribe'])->name('plans.subscribe');

    // Course routes that require authentication
    Route::get('/cursos/{slug}/certificado', [CourseController::class, 'certificate'])->name('courses.certificate');
    Route::get('/cursos/{slug}/{lessonSlug}', [CourseController::class, 'attend'])->name('courses.attend');
    Route::post('/cursos/{slug}/{lessonSlug}/complete', [CourseController::class, 'completeLesson'])->name('courses.complete');
});

Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
Route::get('/cursos/{slug}', [CourseController::class, 'show'])->name('courses.show');
Route::get('/{slug}', [BlogController::class, 'getBySlug']);