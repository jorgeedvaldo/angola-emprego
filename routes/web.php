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

Route::get('/{slug}', [BlogController::class, 'getBySlug']);
Route::get('/vagas/{slug}', [JobController::class, 'getBySlug']);

Route::get('/sitemap.xml', [HomeController::class, 'siteMapGenerator'])->name('sitemap');
Route::get('/feed', [HomeController::class, 'feedGenerator'])->name('feed');
