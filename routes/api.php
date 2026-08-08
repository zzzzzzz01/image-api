<?php

use App\Http\Controllers\ImageController;
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

Route::post('/register', [ImageController::class, 'register']);
Route::post('/login', [ImageController::class, 'login']);

// MUHIM: Barcha maxfiy routlar auth:sanctum (Token) bilan himoyalangan
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ImageController::class, 'logout']);
    Route::post('/upload', [ImageController::class, 'upload']);
    Route::get('/images', [ImageController::class, 'images']);
    Route::delete('/delete/{id}', [ImageController::class, 'delete']);
});
