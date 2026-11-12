<?php

use App\Http\Controllers\Api\V1\Auth\SessionController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::get('/media', [MediaController::class, 'show'])
    ->middleware('throttle:api-download')
    ->name('media.show');

Route::get('/ping', fn () => ApiResponse::success([
    'ok' => true,
    'version' => config('api.version', '1.0.0'),
]))->name('ping');

Route::post('/auth/sessions/password', [SessionController::class, 'password'])
    ->middleware('throttle:api-login')
    ->name('auth.sessions.password');

Route::post('/auth/sessions/google', [SessionController::class, 'google'])
    ->middleware('throttle:api-google')
    ->name('auth.sessions.google');
