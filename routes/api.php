<?php

use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\SubscriberController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/subscribers', [SubscriberController::class, 'index']);
Route::post('/job/create', [JobController::class, 'store']);
Route::post('/post/create', [PostController::class, 'store']);

Route::get('/jobs/{id}', [JobController::class, 'getById']);
Route::get('/users', [App\Http\Controllers\Api\UserController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['get', 'post'], '/pagamento/callback', [App\Http\Controllers\Api\PaymentCallbackController::class, 'handle']);
