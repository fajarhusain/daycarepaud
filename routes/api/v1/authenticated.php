<?php

use App\Http\Controllers\Api\V1\Auth\SessionController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PendaftaranAnakController;
use App\Http\Controllers\Api\V1\SekolahController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::delete('/auth/sessions/current', [SessionController::class, 'destroy'])
        ->name('auth.sessions.current.destroy');

    Route::get('/me', [MeController::class, 'show'])
        ->name('me.show');

    Route::get('/me/sekolah', [SekolahController::class, 'mine'])
        ->name('me.sekolah');

    Route::get('/sekolah', [SekolahController::class, 'index'])
        ->name('sekolah.index');

    Route::middleware('api.role:orang-tua')->group(function () {
        Route::patch('/me/orang-tua', [MeController::class, 'updateOrangTua'])
            ->name('me.orang-tua.update');

        Route::get('/pendaftaran-anak', [PendaftaranAnakController::class, 'index'])
            ->name('pendaftaran-anak.index');
        Route::post('/pendaftaran-anak', [PendaftaranAnakController::class, 'store'])
            ->middleware('throttle:api-upload')
            ->name('pendaftaran-anak.store');
    });
});
