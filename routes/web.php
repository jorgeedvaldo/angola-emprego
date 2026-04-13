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
Route::get('/linkstorage', function () {
    // Cria o link simbólico (storage -> public)
    Artisan::call('migrate');

    return 'Symlink criado: <pre>' . Artisan::output() . '</pre>';
});
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sobre', [AboutController::class, 'index'])->name('sobre');
Route::get('/vagas', [JobController::class, 'index'])->name('vagas');
Route::get('/blog', [BlogController::class, 'index']);
Route::get('/noticias', [BlogController::class, 'index']);
Route::get('/atm-com-dinheiro', [ToolsController::class, 'index']);
Route::get('/sitemap.xml', [HomeController::class, 'siteMapGenerator'])->name('sitemap');
Route::get('/feed', [HomeController::class, 'feedGenerator'])->name('feed');


// Public profile by username (e.g. /@joao.silva)
Route::get('/@{username}', [App\Http\Controllers\ProfileController::class, 'publicProfile'])->name('profile.public');

Route::get('/vagas/{slug}', [JobController::class, 'getBySlug']);

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // Google Auth
    Route::get('auth/google', [App\Http\Controllers\Auth\GoogleController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('auth/google/callback', [App\Http\Controllers\Auth\GoogleController::class, 'handleGoogleCallback']);
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile & Recommendations
    Route::get('/perfil', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/perfil', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/perfil/cv/{id}/primary', [App\Http\Controllers\ProfileController::class, 'setPrimaryCv'])->name('profile.cv.primary');
    Route::delete('/perfil/cv/{id}', [App\Http\Controllers\ProfileController::class, 'deleteCv'])->name('profile.cv.delete');
    Route::get('/vagas-sugeridas', [App\Http\Controllers\ProfileController::class, 'potentialJobs'])->name('jobs.potential');
    Route::get('/planos', [App\Http\Controllers\ProfileController::class, 'plans'])->name('plans.index');
    Route::get('/planos/confirmar', [App\Http\Controllers\ProfileController::class, 'confirm'])->name('plans.confirm');
    Route::post('/planos/subscrever', [App\Http\Controllers\ProfileController::class, 'subscribe'])->name('plans.subscribe');
    Route::post('/planos/subscrever-referencia', [App\Http\Controllers\ProfileController::class, 'subscribeReference'])->name('plans.subscribereference');
    Route::get('/planos/status/{id}', [App\Http\Controllers\ProfileController::class, 'checkStatus'])->name('subscription.check_status');

    // Profile Extended Sections
    Route::put('/perfil/bio', [App\Http\Controllers\ProfileController::class, 'updateBio'])->name('profile.bio.update');
    Route::post('/perfil/habilidades', [App\Http\Controllers\ProfileController::class, 'storeSkill'])->name('profile.skills.store');
    Route::delete('/perfil/habilidades/{id}', [App\Http\Controllers\ProfileController::class, 'deleteSkill'])->name('profile.skills.delete');
    Route::post('/perfil/formacao', [App\Http\Controllers\ProfileController::class, 'storeEducation'])->name('profile.education.store');
    Route::delete('/perfil/formacao/{id}', [App\Http\Controllers\ProfileController::class, 'deleteEducation'])->name('profile.education.delete');
    Route::post('/perfil/experiencia', [App\Http\Controllers\ProfileController::class, 'storeExperience'])->name('profile.experience.store');
    Route::delete('/perfil/experiencia/{id}', [App\Http\Controllers\ProfileController::class, 'deleteExperience'])->name('profile.experience.delete');
    Route::post('/perfil/idiomas', [App\Http\Controllers\ProfileController::class, 'storeLanguage'])->name('profile.languages.store');
    Route::delete('/perfil/idiomas/{id}', [App\Http\Controllers\ProfileController::class, 'deleteLanguage'])->name('profile.languages.delete');

    // Course routes that require authentication
    Route::get('/cursos/{slug}/certificado/preview', [CourseController::class, 'previewCertificate'])->name('courses.certificate.preview');
    Route::get('/cursos/{slug}/certificado', [CourseController::class, 'downloadCertificate'])->name('courses.certificate');
    Route::get('/cursos/{slug}/{lessonSlug}', [CourseController::class, 'attend'])->name('courses.attend');
    Route::post('/cursos/{slug}/{lessonSlug}/complete', [CourseController::class, 'completeLesson'])->name('courses.complete');

    // Payment Confirmation
    Route::get('/pagamento/sucesso', [App\Http\Controllers\PaymentController::class, 'success'])->name('payment.success');
});

Route::get('/cursos', [CourseController::class, 'index'])->name('courses.index');
Route::get('/certificado/validar/{user}/{course}', [CourseController::class, 'verifyCertificate'])->name('certificates.verify');
Route::get('/cursos/{slug}', [CourseController::class, 'show'])->name('courses.show');
// [CSMJ] Rota estática para resultados do concurso público CSMJ 2026 — REMOVER QUANDO NECESSÁRIO
Route::get('/noticias/resultados-concurso-csmj-2026', function () {
    $categories = \App\Models\Category::all();
    return view('postresultado', compact('categories'));
})->name('post.resultado.csmj');

Route::get('/noticias/{slug}', [BlogController::class, 'getBySlug']);
Route::get('/{slug}', [BlogController::class, 'getBySlug']);