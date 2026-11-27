<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GoogleAuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/auth/google/redirect', [
    GoogleAuthController::class,
    'redirect'
]);

Route::get('/auth/google/callback', [
    GoogleAuthController::class,
    'callback'
]);
