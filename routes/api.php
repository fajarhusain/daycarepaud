<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GuestBerandaController;
use App\Http\Controllers\Api\V1\OrangTua\AnakController as OrtuAnakController;
use App\Http\Controllers\Api\V1\OrangTua\KegiatanController as OrtuKegiatanController;
use App\Http\Controllers\Api\V1\OrangTua\KritikSaranController as OrtuKritikSaranController;
use App\Http\Controllers\Api\V1\OrangTua\MenuMakananController as OrtuMenuMakananController;
use App\Http\Controllers\Api\V1\OrangTua\PencapaianController as OrtuPencapaianController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['api.request-id', 'api.locale'])
    ->group(function () {
        require __DIR__.'/api/v1/public.php';
        require __DIR__.'/api/v1/authenticated.php';
        require __DIR__.'/api/v1/orang-tua.php';

        /*
        |--------------------------------------------------------------------------
        | Legacy API v1
        |--------------------------------------------------------------------------
        |
        | Dipertahankan apa adanya untuk kompatibilitas client lama. Mobile hanya
        | memakai endpoint baru. Alias dan header deprecation ditangani pada fase
        | kompatibilitas.
        |
        */

        Route::get('/guest/beranda', [GuestBerandaController::class, 'show']);

        Route::post('/auth/login', [AuthController::class, 'login'])
            ->middleware('throttle:12,1');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::get('/auth/user', [AuthController::class, 'user']);
            Route::patch('/auth/active-sekolah', [AuthController::class, 'updateActiveSekolah']);

            Route::middleware('role:Orang Tua')->prefix('orang-tua')->group(function () {
                Route::get('anak', [OrtuAnakController::class, 'index']);
                Route::patch('anak/{anak}/nickname', [OrtuAnakController::class, 'updateNickname']);
                Route::get('kegiatan', [OrtuKegiatanController::class, 'index']);
                Route::get('menu-makanan', [OrtuMenuMakananController::class, 'index']);
                Route::get('pencapaian', [OrtuPencapaianController::class, 'index']);

                Route::get('kritik-saran', [OrtuKritikSaranController::class, 'index']);
                Route::post('kritik-saran', [OrtuKritikSaranController::class, 'store']);
                Route::get('kritik-saran/{id}', [OrtuKritikSaranController::class, 'show']);
            });
        });
    });
