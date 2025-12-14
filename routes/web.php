<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AboutController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ToolsController;

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

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::group(['middleware' => 'auth'], function () {
    Route::get('/cursos/{slug}/certificado', [App\Http\Controllers\CourseController::class, 'certificate'])->name('courses.certificate');
    Route::get('/cursos/{slug}/{lessonSlug}', [App\Http\Controllers\CourseController::class, 'attend'])->name('courses.attend');
    Route::post('/cursos/{slug}/{lessonSlug}/complete', [App\Http\Controllers\CourseController::class, 'completeLesson'])->name('courses.complete');
});

Route::get('/cursos', [App\Http\Controllers\CourseController::class, 'index'])->name('courses.index');
Route::get('/cursos/{slug}', [App\Http\Controllers\CourseController::class, 'show'])->name('courses.show');


Route::get('/{slug}', [BlogController::class, 'getBySlug']);