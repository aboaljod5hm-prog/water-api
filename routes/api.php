<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// ── Public ──
Route::post('/login', [AuthController::class, 'login']);

// ── Protected ──
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
    Route::get('/subscribers/stats', [SubscriberController::class, 'stats']);
    // Subscribers
    Route::get('/subscribers/export',        [SubscriberController::class, 'export']);
    Route::post('/subscribers/import',       [SubscriberController::class, 'import']);
    Route::get('/subscribers',               [SubscriberController::class, 'index']);
    Route::post('/subscribers',              [SubscriberController::class, 'store']);
    Route::put('/subscribers/{subscriber}',  [SubscriberController::class, 'update']);
    Route::delete('/subscribers',            [SubscriberController::class, 'destroyAll']);
    Route::delete('/subscribers/{subscriber}', [SubscriberController::class, 'destroy']);
    // Users (admin_general only)
    Route::get('/users',         [UserController::class, 'index']);
    Route::post('/users',        [UserController::class, 'store']);
    Route::put('/users/{user}',  [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
