<?php

use App\Http\Controllers\Api\V1\Sekolah\AnakController;
use App\Http\Controllers\Api\V1\Sekolah\ContextController;
use App\Http\Controllers\Api\V1\Sekolah\KegiatanController;
use App\Http\Controllers\Api\V1\Sekolah\KegiatanRutinController;
use App\Http\Controllers\Api\V1\Sekolah\KesehatanController;
use App\Http\Controllers\Api\V1\Sekolah\MasukanController;
use App\Http\Controllers\Api\V1\Sekolah\MenuMakananController;
use App\Http\Controllers\Api\V1\Sekolah\MonevController;
use App\Http\Controllers\Api\V1\Sekolah\PencapaianController;
use App\Http\Controllers\Api\V1\Sekolah\PengumumanController;
use App\Http\Controllers\Api\V1\Sekolah\PresensiController;
use App\Http\Controllers\Api\V1\Sekolah\TagihanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api', 'api.tenant', 'api.role:orang-tua'])
    ->prefix('sekolah/{sekolah}')
    ->name('orang-tua.')
    ->group(function () {
        Route::get('/context', [ContextController::class, 'show'])
            ->name('context.show');

        Route::get('/anak', [AnakController::class, 'index'])->name('anak.index');
        Route::get('/anak/{anak}', [AnakController::class, 'show'])->name('anak.show');
        Route::patch('/anak/{anak}', [AnakController::class, 'update'])->name('anak.update');

        Route::get('/pencapaian', [PencapaianController::class, 'index'])->name('pencapaian.index');
        Route::get('/pencapaian/{pencapaian}', [PencapaianController::class, 'show'])->name('pencapaian.show');

        Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan.index');
        Route::get('/kegiatan/{kegiatan}', [KegiatanController::class, 'show'])->name('kegiatan.show');

        Route::get('/kegiatan-rutin', [KegiatanRutinController::class, 'index'])->name('kegiatan-rutin.index');
        Route::get('/kegiatan-rutin/{kegiatan_rutin}', [KegiatanRutinController::class, 'show'])->name('kegiatan-rutin.show');

        Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');

        Route::get('/kesehatan', [KesehatanController::class, 'index'])->name('kesehatan.index');
        Route::get('/kesehatan/{kesehatan}', [KesehatanController::class, 'show'])->name('kesehatan.show');

        Route::get('/monev', [MonevController::class, 'index'])->name('monev.index');
        Route::get('/monev/{monev}/pdf', [MonevController::class, 'pdf'])->name('monev.pdf');
        Route::get('/monev/{monev}', [MonevController::class, 'show'])->name('monev.show');

        Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
        Route::get('/tagihan/ringkasan', [TagihanController::class, 'summary'])->name('tagihan.summary');
        Route::get('/tagihan/{tagihan}/invoice', [TagihanController::class, 'invoice'])
            ->middleware('throttle:api-download')
            ->name('tagihan.invoice');
        Route::get('/tagihan/{tagihan}/bukti-pembayaran', [TagihanController::class, 'proof'])
            ->name('tagihan.bukti-pembayaran.show');
        Route::post('/tagihan/{tagihan}/bukti-pembayaran', [TagihanController::class, 'uploadProof'])
            ->middleware(['throttle:api-upload', 'api.idempotency'])
            ->name('tagihan.bukti-pembayaran.store');
        Route::get('/tagihan/{tagihan}', [TagihanController::class, 'show'])->name('tagihan.show');

        Route::get('/menu-makanan', [MenuMakananController::class, 'index'])->name('menu-makanan.index');
        Route::get('/menu-makanan/{menu_makanan}', [MenuMakananController::class, 'show'])->name('menu-makanan.show');
        Route::put('/menu-makanan/{menu_makanan}/vote', [MenuMakananController::class, 'vote'])->name('menu-makanan.vote');
        Route::delete('/menu-makanan/{menu_makanan}/vote', [MenuMakananController::class, 'removeVote'])->name('menu-makanan.vote.destroy');

        Route::get('/masukan', [MasukanController::class, 'index'])->name('masukan.index');
        Route::post('/masukan', [MasukanController::class, 'store'])
            ->middleware('throttle:api-upload')
            ->name('masukan.store');
        Route::get('/masukan/{masukan}', [MasukanController::class, 'show'])->name('masukan.show');
        Route::patch('/masukan/{masukan}', [MasukanController::class, 'update'])
            ->middleware('throttle:api-upload')
            ->name('masukan.update');
        Route::delete('/masukan/{masukan}', [MasukanController::class, 'destroy'])->name('masukan.destroy');

        Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
        Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
        Route::put('/pengumuman/{pengumuman}/dibaca', [PengumumanController::class, 'markAsRead'])->name('pengumuman.dibaca');
    });
