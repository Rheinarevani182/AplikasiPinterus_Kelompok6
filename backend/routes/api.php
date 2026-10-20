<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PinController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:6,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me/password', [AuthController::class, 'updatePassword']);
    Route::get('/me/reports', [UserController::class, 'reports']);
    Route::patch('/me', [UserController::class, 'update']);
    Route::get('/me/saved-pins', [PinController::class, 'saved']);

    Route::get('/pins', [PinController::class, 'index']);
    Route::post('/pins', [PinController::class, 'store']);
    Route::delete('/pins/{pin}', [PinController::class, 'destroy']);
    Route::post('/pins/{pin}/save', [PinController::class, 'save']);
    Route::delete('/pins/{pin}/save', [PinController::class, 'unsave']);
    Route::post('/pins/{pin}/report', [PinController::class, 'report']);

    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::post('/users/{user}/follow', [UserController::class, 'follow']);
    Route::delete('/users/{user}/follow', [UserController::class, 'unfollow']);
});
