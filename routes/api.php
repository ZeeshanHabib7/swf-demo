<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MovieController;
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

// User Authentication
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
// Movie Routes
Route::middleware('auth:sanctum')->group(function () {
    // List all Star Wars movies
    Route::get('movies', [MovieController::class, 'index']);

    // Get detailed information about a specific movie
    Route::get('movies/{id}', [MovieController::class, 'show']);

    // Update a movie
    Route::put('movies/{id}', [MovieController::class, 'update']);

    // Delete a movie
    Route::delete('movies/{id}', [MovieController::class, 'destroy']);
});
