<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExportController;
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

// Columns
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/columns', [ColumnController::class, 'index']);
    Route::post('/columns', [ColumnController::class, 'store']);
    Route::delete('/columns/{column}', [ColumnController::class, 'destroy']);

    // Cards
    Route::post('/cards', [CardController::class, 'store']);
    Route::get('/cards/{card}', [CardController::class, 'show']);
    Route::put('/cards/{card}', [CardController::class, 'update']);
    Route::delete('/cards/{card}', [CardController::class, 'destroy']);
    Route::put('/move-card', [CardController::class, 'moveCard']);
    Route::put('/move-card-to-other-column', [CardController::class, 'moveCardToOtherColumn']);

    // export-database
    Route::get('/export-database', [ExportController::class, 'exportDatabase']);
});

// Authentication Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
