<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoogleAuthController;
use App\Http\Controllers\DBTransactionController;
use App\Http\Controllers\Api\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google/exchange', [GoogleAuthController::class, 'exchange']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::prefix('user-profile')->group(function () {
        Route::post('/store', [UserProfileController::class, 'store']);
    });

    Route::middleware('has.profile')->group(function (): void {
        Route::get('/user', [AuthController::class, 'user']);



    });
});

if (app()->isLocal()) {
    Route::post('/test-transaction', [DBTransactionController::class, 'testTransaction']);
}
