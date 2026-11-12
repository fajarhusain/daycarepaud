<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\AiPersonaController;
use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\AkuntansiSettingController;
use App\Http\Controllers\Admin\AnakController;
use App\Http\Controllers\Admin\BiayaBulananController;
use App\Http\Controllers\Admin\CashflowController;
use App\Http\Controllers\Admin\DiskonController;
use App\Http\Controllers\Admin\GajiGuruController;
use App\Http\Controllers\Admin\JamKerjaController;
use App\Http\Controllers\Admin\KomponenGajiController;
use App\Http\Controllers\Admin\JurnalController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\KegiatanRutinController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\KesehatanController;
// Lembaga Controllers
use App\Http\Controllers\Admin\KritikSaranController as AdminKritikSaranController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MasterKegiatanRutinController;
use App\Http\Controllers\Admin\MatrikulasiController as AdminMatrikulasiController;
use App\Http\Controllers\Admin\MenuMakananController;
use App\Http\Controllers\Admin\MonevController as AdminMonevController;
use App\Http\Controllers\Admin\MonevGuruEvaluasiController;
use App\Http\Controllers\Admin\MonevGuruKriteriaController;
use App\Http\Controllers\Admin\OrangTuaChatController;
use App\Http\Controllers\Admin\OrangTuaSearchController;
// Admin Sekolah Controllers
use App\Http\Controllers\Admin\PembayaranBulananController;
use App\Http\Controllers\Admin\PendaftaranController;
use App\Http\Controllers\Admin\PengajarController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PengumumanController;
use App\Http\Controllers\Admin\PresensiController;
use App\Http\Controllers\Admin\PresensiPengajarController;
use App\Http\Controllers\Admin\RkasController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SaranaController;
use App\Http\Controllers\Admin\SkalaPencapaianController;
use App\Http\Controllers\Admin\SumberDanaController;
use App\Http\Controllers\Admin\TabunganInvestasiController;
use App\Http\Controllers\AdminKelas\AnakController as AdminKelasAnakController;
use App\Http\Controllers\AdminKelas\MonevController as AdminKelasMonevController;
use App\Http\Controllers\AdminKelas\PresensiController as AdminKelasPresensiController;
use App\Http\Controllers\Api\AiFeedbackController;
use App\Http\Controllers\Api\AiMonevGuruController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Guest\LembagaRegistrationController;
use App\Http\Controllers\Guest\SekolahRegistrationController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestSitemapController;
use App\Http\Controllers\Lembaga\AdminSekolahController;
use App\Http\Controllers\Lembaga\KritikSaranController as LembagaKritikSaranController;
use App\Http\Controllers\Lembaga\SchoolSwitcherController;
use App\Http\Controllers\Lembaga\SekolahController;
use App\Http\Controllers\OrangTua\AnakController as OrangTuaAnakController;
use App\Http\Controllers\OrangTua\ChatController;
use App\Http\Controllers\OrangTua\KegiatanController as OrangTuaKegiatanController;
use App\Http\Controllers\OrangTua\KritikSaranController as OrangTuaKritikSaranController;
use App\Http\Controllers\OrangTua\MenuMakananController as OrangTuaMenuMakananController;
use App\Http\Controllers\OrangTua\MenuMakananVoteController;
use App\Http\Controllers\OrangTua\MonevController;
use App\Http\Controllers\OrangTua\PembayaranController;
use App\Http\Controllers\OrangTua\PencapaianController as OrangTuaPencapaianController;
use App\Http\Controllers\OrangTua\PengumumanController as OrangTuaPengumumanController;
use App\Http\Controllers\OrangTua\SchoolSwitcherController as OrangTuaSchoolSwitcherController;
use App\Http\Controllers\Pengajar\KegiatanController as PengajarKegiatanController;
use App\Http\Controllers\Pengajar\MatrikulasiController;
use App\Http\Controllers\Pengajar\MonevGuruController as PengajarMonevGuruController;
use App\Http\Controllers\Pengajar\PencapaianController;
use App\Http\Controllers\Pengajar\PresensiController as PengajarPresensiController;
use App\Http\Controllers\Pengajar\PresensiGuruController as PengajarPresensiGuruController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Superadmin\AdminLembagaController as SuperadminAdminLembagaController;
use App\Http\Controllers\Superadmin\AiSettingController as SuperadminAiSettingController;
use App\Http\Controllers\Superadmin\CmsController as SuperadminCmsController;
use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\LembagaController as SuperadminLembagaController;
use App\Http\Controllers\Superadmin\SekolahController as SuperadminSekolahController;
use App\Http\Controllers\Superadmin\SuperadminUserController;
use App\Http\Controllers\TourController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────
// PUBLIC GUEST ROUTES
// ─────────────────────────────────────────────
Route::get('/sitemap.xml', [GuestSitemapController::class, 'index'])->name('guest.sitemap');
Route::get('/', [GuestController::class, 'beranda'])->name('guest.beranda');
Route::get('/tentang', [GuestController::class, 'tentang'])->name('guest.tentang');
Route::get('/fasilitas', [GuestController::class, 'fasilitas'])->name('guest.fasilitas');
Route::get('/harga', [GuestController::class, 'harga'])->name('guest.harga');
Route::redirect('/galeri', '/harga', 301);
Route::get('/pendaftaran', [GuestController::class, 'pendaftaran'])->name('guest.pendaftaran');
Route::post('/pendaftaran', [RegisteredUserController::class, 'store'])
    ->middleware(['guest', 'throttle:guest-forms'])
    ->name('guest.pendaftaran.store');
Route::get('/kontak', [GuestController::class, 'kontak'])->name('guest.kontak');
Route::post('/kontak', [GuestController::class, 'kontakSend'])
    ->middleware('throttle:guest-forms')
    ->name('guest.kontak.send');
Route::get('/daftar-sekolah', [SekolahRegistrationController::class, 'create'])->name('guest.daftar-sekolah');
Route::post('/daftar-sekolah', [SekolahRegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:guest-forms'])
    ->name('guest.daftar-sekolah.store');
Route::redirect('/daftar-lembaga', '/daftar-sekolah', 301);
Route::post('/daftar-lembaga', [LembagaRegistrationController::class, 'store'])
    ->middleware(['guest', 'throttle:guest-forms'])
    ->name('guest.daftar-lembaga.store');

// ─────────────────────────────────────────────
// AUTH REQUIRED
// ─────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant.context'])
    ->name('dashboard');

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/sekolah', [ProfileController::class, 'editSekolah'])->name('profile.sekolah.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('/profile/sekolah', [ProfileController::class, 'updateSekolah'])->name('profile.sekolah.update');
    Route::patch('/profile/pengajar', [ProfileController::class, 'updatePengajar'])->name('profile.pengajar.update');
    Route::patch('/profile/orangtua', [ProfileController::class, 'updateOrangTua'])->name('profile.orangtua.update');
    Route::patch('/profile/anak/{anak}', [ProfileController::class, 'updateAnak'])->name('profile.anak.update');

    Route::post('/tour/complete', [TourController::class, 'complete'])
        ->middleware('throttle:30,1')
        ->name('tour.complete');
});

// ─────────────────────────────────────────────
// SUPERADMIN
// ─────────────────────────────────────────────
Route::middleware(['auth', 'role:Superadmin', 'admin.activity'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('lembaga', SuperadminLembagaController::class)->except(['create', 'edit', 'show']);
    Route::resource('admin-lembaga', SuperadminAdminLembagaController::class)->except(['create', 'edit', 'show']);
    Route::resource('users', SuperadminUserController::class)->except(['create', 'edit', 'show']);
    Route::resource('sekolah', SuperadminSekolahController::class)->except(['create', 'edit', 'show']);
    Route::post('sekolah/{sekolah}/approve', [SuperadminSekolahController::class, 'approve'])->name('sekolah.approve');
    Route::post('sekolah/{sekolah}/reject', [SuperadminSekolahController::class, 'reject'])->name('sekolah.reject');
    Route::get('cms', [SuperadminCmsController::class, 'index'])->name('cms.index');
    Route::post('cms', [SuperadminCmsController::class, 'update'])->name('cms.update');
    Route::get('ai-setting', [SuperadminAiSettingController::class, 'index'])->name('ai-setting.index');
    Route::post('ai-setting', [SuperadminAiSettingController::class, 'update'])->name('ai-setting.update');
    Route::post('ai-setting/test', [SuperadminAiSettingController::class, 'testConnection'])->name('ai-setting.test');
    Route::post('ai-setting/tokens', [SuperadminAiSettingController::class, 'storeTokens'])->name('ai-setting.tokens.store');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
    Route::post('lembaga/{lembaga}/approve', [SuperadminLembagaController::class, 'approve'])->name('lembaga.approve');
    Route::post('lembaga/{lembaga}/reject', [SuperadminLembagaController::class, 'reject'])->name('lembaga.reject');
});

// ─────────────────────────────────────────────
// LEMBAGA
// ─────────────────────────────────────────────
Route::middleware(['auth', 'role:Lembaga', 'admin.activity'])->prefix('lembaga')->name('lembaga.')->group(function () {
    Route::post('active-sekolah', [SchoolSwitcherController::class, 'update'])->name('active-sekolah.update');
    Route::resource('sekolah', SekolahController::class)->except(['create', 'edit', 'show']);
    Route::resource('admin-sekolah', AdminSekolahController::class)->except(['create', 'edit', 'show']);
    Route::get('kritik-saran', [LembagaKritikSaranController::class, 'index'])->name('kritik-saran.index');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// ─────────────────────────────────────────────
// ADMIN SEKOLAH
// ─────────────────────────────────────────────
Route::middleware(['auth', 'tenant.context', 'admin.menu', 'lembaga.sekolah', 'sekolah.active', 'admin.activity'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('pengaturan', function (Request $request) {
        $route = \App\Support\AdminSettingsNav::firstRouteName($request->user());
        if ($route === null) {
            abort(403);
        }

        return redirect()->route($route);
    })->name('settings');

    // Export Excel
    Route::get('anak/export', [AnakController::class, 'export'])->name('anak.export');
    Route::get('pendaftaran/export', [PendaftaranController::class, 'export'])->name('pendaftaran.export');
    Route::get('kelas/export', [KelasController::class, 'export'])->name('kelas.export');
    Route::get('presensi/export', [PresensiController::class, 'export'])->name('presensi.export');
    Route::get('presensi/rekap/export', [PresensiController::class, 'exportRekap'])->name('presensi.rekap.export');
    Route::get('presensi-guru/export', [PresensiPengajarController::class, 'export'])->name('presensi-guru.export');
    Route::get('kesehatan/export', [KesehatanController::class, 'export'])->name('kesehatan.export');
    Route::get('matrikulasi/export', [AdminMatrikulasiController::class, 'export'])->name('matrikulasi.export');
    Route::get('matrikulasi/import/template', [AdminMatrikulasiController::class, 'downloadTemplate'])->name('matrikulasi.import.template');
    Route::post('matrikulasi/import/test', [AdminMatrikulasiController::class, 'testImport'])->name('matrikulasi.import.test');
    Route::post('matrikulasi/import', [AdminMatrikulasiController::class, 'import'])->name('matrikulasi.import');
    Route::get('skala-pencapaian/export', [SkalaPencapaianController::class, 'export'])->name('skala-pencapaian.export');
    Route::get('kegiatan/export', [KegiatanController::class, 'export'])->name('kegiatan.export');
    Route::get('kegiatan/photos/download', [KegiatanController::class, 'downloadPhotos'])->name('kegiatan.photos.download');
    Route::get('kegiatan/{kegiatan}/photos/download', [KegiatanController::class, 'downloadPhotosSingle'])->name('kegiatan.photos.download-single');
    Route::get('master-kegiatan-rutin/export', [MasterKegiatanRutinController::class, 'export'])->name('master-kegiatan-rutin.export');
    Route::get('kegiatan-rutin/export', [KegiatanRutinController::class, 'export'])->name('kegiatan-rutin.export');
    Route::get('kegiatan-rutin/photos/download', [KegiatanRutinController::class, 'downloadPhotos'])->name('kegiatan-rutin.photos.download');
    Route::get('kegiatan-rutin/{kegiatan_rutin}/photo/download', [KegiatanRutinController::class, 'downloadPhoto'])->name('kegiatan-rutin.photo.download');
    Route::get('pencapaian/export', [App\Http\Controllers\Admin\PencapaianController::class, 'export'])->name('pencapaian.export');
    Route::get('pencapaian/photos/download', [App\Http\Controllers\Admin\PencapaianController::class, 'downloadPhotos'])->name('pencapaian.photos.download');
    Route::get('pencapaian/photos/download-bundle', [App\Http\Controllers\Admin\PencapaianController::class, 'downloadBundlePhoto'])->name('pencapaian.photos.download-bundle');
    Route::get('monev/export', [AdminMonevController::class, 'export'])->name('monev.export');
    Route::get('monev-guru/export', [MonevGuruEvaluasiController::class, 'export'])->name('monev-guru.export');
    Route::get('pengajar/export', [PengajarController::class, 'export'])->name('pengajar.export');
    Route::get('sarana/export', [SaranaController::class, 'export'])->name('sarana.export');
    Route::get('sarana/{sarana}/photo/download', [SaranaController::class, 'downloadPhoto'])->name('sarana.photo.download');
    Route::get('akun/export', [AkunController::class, 'export'])->name('akun.export');
    Route::get('akun/import/template', [AkunController::class, 'importTemplate'])->name('akun.import.template');
    Route::post('akun/import/test', [AkunController::class, 'testImport'])->name('akun.import.test');
    Route::post('akun/import', [AkunController::class, 'import'])->name('akun.import');
    Route::get('cashflow/export', [CashflowController::class, 'export'])->name('cashflow.export');
    Route::get('tabungan-investasi', [TabunganInvestasiController::class, 'index'])->name('tabungan-investasi.index');
    Route::put('tabungan-investasi/settings', [TabunganInvestasiController::class, 'updateSettings'])->name('tabungan-investasi.settings');
    Route::post('tabungan-investasi/mutasi', [TabunganInvestasiController::class, 'mutasi'])->name('tabungan-investasi.mutasi');
    Route::get('jurnal/export', [JurnalController::class, 'export'])->name('jurnal.export');
    Route::get('sumber-dana/export', [SumberDanaController::class, 'export'])->name('sumber-dana.export');
    Route::get('rkas/export', [RkasController::class, 'export'])->name('rkas.export');
    Route::get('rkas/laporan/export', [RkasController::class, 'exportLaporan'])->name('rkas.laporan.export');
    Route::get('biaya-bulanan/export', [BiayaBulananController::class, 'export'])->name('biaya-bulanan.export');
    Route::get('pembayaran-bulanan/export', [PembayaranBulananController::class, 'export'])->name('pembayaran-bulanan.export');
    Route::get('kritik-saran/export', [AdminKritikSaranController::class, 'export'])->name('kritik-saran.export');
    Route::get('pengguna/export', [PenggunaController::class, 'export'])->name('pengguna.export');
    Route::get('activity-log/export', [ActivityLogController::class, 'export'])->name('activity-log.export');

    Route::get('monev', [AdminMonevController::class, 'index'])->name('monev.index');
    Route::post('monev/generate', [AdminMonevController::class, 'generate'])->middleware('throttle:10,1')->name('monev.generate');
    Route::post('monev/bulk-generate', [AdminMonevController::class, 'bulkGenerate'])->middleware('throttle:10,1')->name('monev.bulk-generate');
    Route::post('monev/bulk-reset', [AdminMonevController::class, 'bulkReset'])->middleware('throttle:20,1')->name('monev.bulk-reset');
    Route::get('monev/generation/{generation}/status', [AdminMonevController::class, 'generationStatus'])->name('monev.generation.status');
    Route::get('monev/{anak}/pdf', [AdminMonevController::class, 'exportPdf'])->name('monev.export-pdf');
    Route::get('monev/{anak}', [AdminMonevController::class, 'show'])->name('monev.show');
    Route::prefix('monev-guru')->name('monev-guru.')->group(function () {
        Route::resource('kriteria', MonevGuruKriteriaController::class)
            ->except(['show'])
            ->parameters(['kriteria' => 'monev_guru_kriteria']);
        Route::post('{monev_guru_evaluasi}/finalize', [MonevGuruEvaluasiController::class, 'finalize'])->name('finalize');
        Route::get('{monev_guru_evaluasi}/pdf', [MonevGuruEvaluasiController::class, 'pdf'])->name('pdf');
        Route::get('/', [MonevGuruEvaluasiController::class, 'index'])->name('index');
        Route::get('create', [MonevGuruEvaluasiController::class, 'create'])->name('create');
        Route::post('/', [MonevGuruEvaluasiController::class, 'store'])->name('store');
        Route::get('{monev_guru_evaluasi}', [MonevGuruEvaluasiController::class, 'show'])->name('show');
        Route::get('{monev_guru_evaluasi}/edit', [MonevGuruEvaluasiController::class, 'edit'])->name('edit');
        Route::put('{monev_guru_evaluasi}', [MonevGuruEvaluasiController::class, 'update'])->name('update');
        Route::delete('{monev_guru_evaluasi}', [MonevGuruEvaluasiController::class, 'destroy'])->name('destroy');
    });
    Route::resource('kelas', KelasController::class)->except(['create', 'edit', 'show']);
    Route::resource('matrikulasi', AdminMatrikulasiController::class)->except(['create', 'edit', 'show']);
    Route::resource('skala-pencapaian', SkalaPencapaianController::class)->except(['create', 'edit', 'show']);
    Route::get('orang-tua/search', OrangTuaSearchController::class)->name('orang-tua.search');
    Route::get('anak/import/template', [AnakController::class, 'downloadTemplate'])->name('anak.import.template');
    Route::post('anak/import/test', [AnakController::class, 'testImport'])->name('anak.import.test');
    Route::post('anak/import', [AnakController::class, 'import'])->name('anak.import');
    Route::resource('anak', AnakController::class)->except(['create', 'edit']);
    Route::resource('sarana', SaranaController::class)->except(['create', 'edit', 'show']);
    Route::get('sarana/{sarana}/photo/download', [SaranaController::class, 'downloadPhoto'])->name('sarana.photo.download');
    Route::resource('pengajar', PengajarController::class)->except(['create', 'edit', 'show']);
    Route::resource('menu-makanan', MenuMakananController::class)->except(['create', 'edit', 'show']);
    Route::get('menu-makanan/{menu_makanan}/photo/download', [MenuMakananController::class, 'downloadPhoto'])->name('menu-makanan.photo.download');
    Route::get('menu-makanan/{menu_makanan}/photo/download', [MenuMakananController::class, 'downloadPhoto'])->name('menu-makanan.photo.download');
    Route::resource('pengumuman', PengumumanController::class)->except(['create', 'edit', 'show']);

    // Biaya Bulanan & Pembayaran
    Route::get('biaya-bulanan', [BiayaBulananController::class, 'index'])->name('biaya-bulanan.index');
    Route::post('biaya-bulanan', [BiayaBulananController::class, 'store'])->name('biaya-bulanan.store');
    Route::put('biaya-bulanan/{biayaBulanan}', [BiayaBulananController::class, 'update'])->name('biaya-bulanan.update');
    Route::delete('biaya-bulanan/{biayaBulanan}', [BiayaBulananController::class, 'destroy'])->name('biaya-bulanan.destroy');
    Route::patch('biaya-bulanan/{biayaBulanan}/siswa', [BiayaBulananController::class, 'updateSiswaBiayaBulanan'])->name('biaya-bulanan.update-siswa');
    Route::post('biaya-bulanan/{biayaBulanan}/siswa', [BiayaBulananController::class, 'addSiswa'])->name('biaya-bulanan.add-siswa');
    Route::delete('biaya-bulanan/{biayaBulanan}/siswa/{anak}', [BiayaBulananController::class, 'removeSiswa'])->name('biaya-bulanan.remove-siswa');

    Route::resource('diskon', DiskonController::class)->except(['create', 'edit', 'show']);

    // Akuntansi PSAK
    Route::get('akun/{akun}/riwayat-jurnal', [AkunController::class, 'riwayatJurnal'])->name('akun.riwayat-jurnal');
    Route::resource('akun', AkunController::class)->except(['create', 'edit', 'show']);

    Route::get('akuntansi-setting', [AkuntansiSettingController::class, 'index'])->name('akuntansi-setting.index');
    Route::put('akuntansi-setting', [AkuntansiSettingController::class, 'update'])->name('akuntansi-setting.update');

    Route::get('jurnal/create', [JurnalController::class, 'create'])->name('jurnal.create');
    Route::resource('jurnal', JurnalController::class)->except(['create', 'edit']);

    Route::get('laporan-keuangan', [LaporanController::class, 'index'])->name('laporan-keuangan.index');
    Route::get('laporan-keuangan/neraca', [LaporanController::class, 'neraca'])->name('laporan-keuangan.neraca');
    Route::get('laporan-keuangan/neraca/export', [LaporanController::class, 'neracaExport'])->name('laporan-keuangan.neraca.export');
    Route::get('laporan-keuangan/neraca/pdf', [LaporanController::class, 'neracaPdf'])->name('laporan-keuangan.neraca.pdf');
    Route::get('laporan-keuangan/laba-rugi', [LaporanController::class, 'labaRugi'])->name('laporan-keuangan.laba-rugi');
    Route::get('laporan-keuangan/laba-rugi/export', [LaporanController::class, 'labaRugiExport'])->name('laporan-keuangan.laba-rugi.export');
    Route::get('laporan-keuangan/laba-rugi/pdf', [LaporanController::class, 'labaRugiPdf'])->name('laporan-keuangan.laba-rugi.pdf');
    Route::get('laporan-keuangan/buku-besar', [LaporanController::class, 'bukuBesar'])->name('laporan-keuangan.buku-besar');
    Route::get('laporan-keuangan/buku-besar/export', [LaporanController::class, 'bukuBesarExport'])->name('laporan-keuangan.buku-besar.export');
    Route::get('laporan-keuangan/buku-besar/pdf', [LaporanController::class, 'bukuBesarPdf'])->name('laporan-keuangan.buku-besar.pdf');

    // RKAS
    Route::resource('sumber-dana', SumberDanaController::class)->except(['create', 'edit', 'show']);
    Route::get('rkas/laporan', [RkasController::class, 'laporan'])->name('rkas.laporan');
    Route::get('rkas/export-pdf', [RkasController::class, 'exportPdf'])->name('rkas.export-pdf');
    Route::post('rkas/{rka}/finalize', [RkasController::class, 'finalize'])->name('rkas.finalize');
    Route::post('rkas/{rka}/reopen', [RkasController::class, 'reopen'])->name('rkas.reopen');
    Route::post('rkas/{rka}/sync', [RkasController::class, 'sync'])->name('rkas.sync');
    Route::put('rkas/{rka}/realisasi', [RkasController::class, 'updateRealisasi'])->name('rkas.update-realisasi');
    Route::resource('rkas', RkasController::class)->except(['create', 'show']);

    // Manajemen Role & Pengguna
    Route::resource('role', RoleController::class)->except(['create', 'edit', 'show']);
    Route::post('role/{role}/update-permissions', [RoleController::class, 'updatePermissions'])->name('role.update-permissions');
    Route::resource('pengguna', PenggunaController::class)->except(['create', 'edit', 'show']);

    Route::get('pembayaran-bulanan', [PembayaranBulananController::class, 'index'])->name('pembayaran-bulanan.index');
    Route::get('pembayaran-bulanan/generate-preview', [PembayaranBulananController::class, 'generatePreview'])->name('pembayaran-bulanan.generate-preview');
    Route::get('pembayaran-bulanan/{pembayaran}', [PembayaranBulananController::class, 'show'])->name('pembayaran-bulanan.show');
    Route::get('pembayaran-bulanan/{pembayaran}/bukti/download', [PembayaranBulananController::class, 'downloadBukti'])->name('pembayaran-bulanan.bukti.download');
    Route::get('pembayaran-bulanan/{pembayaran}/bukti/download', [PembayaranBulananController::class, 'downloadBukti'])->name('pembayaran-bulanan.bukti.download');
    Route::post('pembayaran-bulanan/generate', [PembayaranBulananController::class, 'generate'])->name('pembayaran-bulanan.generate');
    Route::delete('pembayaran-bulanan/{pembayaran}', [PembayaranBulananController::class, 'destroy'])->name('pembayaran-bulanan.destroy');
    Route::patch('pembayaran-bulanan/{pembayaran}/batalkan-lunas', [PembayaranBulananController::class, 'batalkanLunas'])->name('pembayaran-bulanan.batalkan-lunas');
    Route::patch('pembayaran-bulanan/{pembayaran}/diskon', [PembayaranBulananController::class, 'updateDiskon'])->name('pembayaran-bulanan.update-diskon');
    Route::patch('pembayaran-bulanan/{pembayaran}/approve', [PembayaranBulananController::class, 'approve'])->name('pembayaran-bulanan.approve');
    Route::patch('pembayaran-bulanan/{pembayaran}/reject', [PembayaranBulananController::class, 'reject'])->name('pembayaran-bulanan.reject');
    Route::post('pembayaran-bulanan/{pembayaran}/items', [PembayaranBulananController::class, 'storeItem'])->name('pembayaran-bulanan.items.store');
    Route::patch('pembayaran-bulanan/{pembayaran}/items/{item}', [PembayaranBulananController::class, 'updateItem'])->name('pembayaran-bulanan.items.update');
    Route::delete('pembayaran-bulanan/{pembayaran}/items/{item}', [PembayaranBulananController::class, 'destroyItem'])->name('pembayaran-bulanan.items.destroy');

    Route::prefix('gaji-guru')->name('gaji-guru.')->group(function () {
        Route::get('export', [GajiGuruController::class, 'export'])->name('export');
        Route::get('komponen', [KomponenGajiController::class, 'index'])->name('komponen.index');
        Route::post('komponen', [KomponenGajiController::class, 'store'])->name('komponen.store');
        Route::put('komponen/{komponen_gaji}', [KomponenGajiController::class, 'update'])->name('komponen.update');
        Route::delete('komponen/{komponen_gaji}', [KomponenGajiController::class, 'destroy'])->name('komponen.destroy');
        Route::get('create', [GajiGuruController::class, 'create'])->name('create');
        Route::patch('{gaji_pengajar}/approve', [GajiGuruController::class, 'approve'])->name('approve');
        Route::patch('{gaji_pengajar}/batalkan', [GajiGuruController::class, 'batalkan'])->name('batalkan');
        Route::get('/', [GajiGuruController::class, 'index'])->name('index');
        Route::post('/', [GajiGuruController::class, 'store'])->name('store');
        Route::get('{gaji_pengajar}', [GajiGuruController::class, 'show'])->name('show');
        Route::get('{gaji_pengajar}/edit', [GajiGuruController::class, 'edit'])->name('edit');
        Route::put('{gaji_pengajar}', [GajiGuruController::class, 'update'])->name('update');
        Route::delete('{gaji_pengajar}', [GajiGuruController::class, 'destroy'])->name('destroy');
    });

    Route::get('kelas/{kelas}/siswa-modal', [KelasController::class, 'siswaModal'])->name('kelas.siswa-modal');
    Route::get('kelas/{kelas}', [KelasController::class, 'show'])->name('kelas.show');
    Route::resource('kegiatan', KegiatanController::class)->except(['create', 'edit', 'show']);
    Route::post('pencapaian/sync', [App\Http\Controllers\Admin\PencapaianController::class, 'sync'])->name('pencapaian.sync');
    Route::delete('pencapaian/bundle', [App\Http\Controllers\Admin\PencapaianController::class, 'destroyBundle'])->name('pencapaian.destroy-bundle');
    Route::resource('pencapaian', App\Http\Controllers\Admin\PencapaianController::class)->only(['index', 'destroy']);
    Route::get('cashflow/{cashflow}/kwitansi/defaults', [CashflowController::class, 'kwitansiDefaults'])->name('cashflow.kwitansi.defaults');
    Route::post('cashflow/{cashflow}/kwitansi/pdf', [CashflowController::class, 'kwitansiPdf'])->name('cashflow.kwitansi.pdf');
    Route::resource('cashflow', CashflowController::class)->except(['create', 'edit', 'show']);
    Route::get('presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::post('presensi', [PresensiController::class, 'store'])->name('presensi.store');
    Route::get('presensi/rekap', [PresensiController::class, 'rekap'])->name('presensi.rekap');
    Route::post('master-kegiatan-rutin/{master_kegiatan_rutin}/store-rutin', [MasterKegiatanRutinController::class, 'storeRutin'])->name('master-kegiatan-rutin.store-rutin');
    Route::get('master-kegiatan-rutin/detail/{master_kegiatan_rutin}/{anak}', [MasterKegiatanRutinController::class, 'detail'])->name('master-kegiatan-rutin.detail');
    Route::delete('master-kegiatan-rutin/rutin/{kegiatan_rutin}', [MasterKegiatanRutinController::class, 'destroyRutinRecord'])->name('master-kegiatan-rutin.destroy-rutin');
    Route::resource('master-kegiatan-rutin', MasterKegiatanRutinController::class);
    Route::get('kegiatan-rutin', [KegiatanRutinController::class, 'index'])->name('kegiatan-rutin.index');
    Route::post('kegiatan-rutin', [KegiatanRutinController::class, 'store'])->name('kegiatan-rutin.store');
    Route::get('kegiatan-rutin/detail/{anak}', [KegiatanRutinController::class, 'detail'])->name('kegiatan-rutin.detail');
    Route::get('kritik-saran', [AdminKritikSaranController::class, 'index'])->name('kritik-saran.index');
    Route::get('kritik-saran/{kritik_saran}', [AdminKritikSaranController::class, 'show'])->name('kritik-saran.show');
    Route::get('kritik-saran/{kritik_saran}/photo/download', [AdminKritikSaranController::class, 'downloadPhoto'])->name('kritik-saran.photo.download');
    Route::get('kritik-saran/{kritik_saran}/photo/download', [AdminKritikSaranController::class, 'downloadPhoto'])->name('kritik-saran.photo.download');
    Route::patch('kritik-saran/{kritik_saran}', [AdminKritikSaranController::class, 'update'])->name('kritik-saran.update');
    Route::get('orangtua-chat', [OrangTuaChatController::class, 'index'])->name('orangtua-chat.index');
    Route::get('orangtua-chat/{orangtua_chat}', [OrangTuaChatController::class, 'show'])->name('orangtua-chat.show');
    Route::get('ai-persona', [AiPersonaController::class, 'index'])->name('ai-persona.index');
    Route::post('ai-persona', [AiPersonaController::class, 'update'])->name('ai-persona.update');
    Route::post('ai-persona/data-access', [AiPersonaController::class, 'updateDataAccess'])->name('ai-persona.data-access.update');
    Route::post('ai-persona/generate', [AiPersonaController::class, 'generate'])->middleware('throttle:10,1')->name('ai-persona.generate');
    Route::post('ai-persona/fallbacks', [AiPersonaController::class, 'updateFallbacks'])->name('ai-persona.fallbacks.update');
    Route::post('ai-persona/chat-enabled', [AiPersonaController::class, 'updateChatEnabled'])->name('ai-persona.chat-enabled.update');
    // Pendaftaran approval
    Route::get('pendaftaran', [PendaftaranController::class, 'index'])->name('pendaftaran.index');
    Route::post('pendaftaran/{anak}/approve', [PendaftaranController::class, 'approve'])->name('pendaftaran.approve');
    Route::post('pendaftaran/{anak}/reject', [PendaftaranController::class, 'reject'])->name('pendaftaran.reject');
    Route::get('kesehatan/history/{anak}', [KesehatanController::class, 'history'])->name('kesehatan.history');
    Route::resource('kesehatan', KesehatanController::class)->only(['index', 'store', 'destroy']);
    Route::get('presensi-guru', [PresensiPengajarController::class, 'index'])->name('presensi-guru.index');
    Route::post('presensi-guru', [PresensiPengajarController::class, 'store'])->name('presensi-guru.store');
    Route::get('presensi-setting', [JamKerjaController::class, 'index'])->name('presensi-setting.index');
    Route::put('presensi-setting', [JamKerjaController::class, 'update'])->name('presensi-setting.update');
    Route::post('presensi-setting/shifts', [JamKerjaController::class, 'storeShift'])->name('presensi-setting.shifts.store');
    Route::patch('presensi-setting/shifts/{presensiShift}', [JamKerjaController::class, 'updateShift'])->name('presensi-setting.shifts.update');
    Route::delete('presensi-setting/shifts/{presensiShift}', [JamKerjaController::class, 'destroyShift'])->name('presensi-setting.shifts.destroy');
    Route::post('presensi-setting/libur', [JamKerjaController::class, 'storeLibur'])->name('presensi-setting.libur.store');
    Route::delete('presensi-setting/libur/{hariLibur}', [JamKerjaController::class, 'destroyLibur'])->name('presensi-setting.libur.destroy');
    Route::redirect('presensi-guru/pengaturan', '/admin/presensi-setting');
    // AI Feedback Suggestions (web route, uses web session auth)
    Route::post('ai/feedback-suggestions', [AiFeedbackController::class, 'suggest'])->name('ai.feedback-suggestions');
    Route::post('ai/monev-guru-suggestions', [AiMonevGuruController::class, 'suggest'])
        ->middleware('throttle:10,1')
        ->name('ai.monev-guru-suggestions');
    Route::post('ai/monev-guru-ringkasan-suggestions', [AiMonevGuruController::class, 'suggestRingkasan'])
        ->middleware('throttle:10,1')
        ->name('ai.monev-guru-ringkasan-suggestions');
    Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// ─────────────────────────────────────────────
// ADMIN KELAS
// ─────────────────────────────────────────────
Route::middleware(['auth', 'tenant.context', 'sekolah.active', 'role:Wali Kelas'])->prefix('adminkelas')->name('adminkelas.')->group(function () {
    Route::resource('anak', AdminKelasAnakController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('presensi', [AdminKelasPresensiController::class, 'index'])->name('presensi.index');
    Route::post('presensi', [AdminKelasPresensiController::class, 'store'])->name('presensi.store');
    Route::get('pencapaian/photos/download', [PencapaianController::class, 'downloadPhotos'])->name('pencapaian.photos.download');
    Route::get('pencapaian/photos/download-bundle', [PencapaianController::class, 'downloadBundlePhoto'])->name('pencapaian.photos.download-bundle');
    Route::get('kegiatan/photos/download', [PengajarKegiatanController::class, 'downloadPhotos'])->name('kegiatan.photos.download');
    Route::get('kegiatan/{kegiatan}/photos/download', [PengajarKegiatanController::class, 'downloadPhotosSingle'])->name('kegiatan.photos.download-single');
    Route::get('kegiatan-rutin/photos/download', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'downloadPhotos'])->name('kegiatan-rutin.photos.download');
    Route::get('kegiatan-rutin/{kegiatan_rutin}/photo/download', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'downloadPhoto'])->name('kegiatan-rutin.photo.download');
    Route::get('kesehatan/history/{anak}', [App\Http\Controllers\AdminKelas\KesehatanController::class, 'history'])->name('kesehatan.history');
    Route::resource('kesehatan', App\Http\Controllers\AdminKelas\KesehatanController::class)->only(['index', 'store', 'destroy']);
    Route::get('matrikulasi', [MatrikulasiController::class, 'index'])->name('matrikulasi.index');
    Route::get('monev', [AdminKelasMonevController::class, 'index'])->name('monev.index');
    Route::post('monev/generate', [AdminKelasMonevController::class, 'generate'])->middleware('throttle:10,1')->name('monev.generate');
    Route::post('monev/bulk-generate', [AdminKelasMonevController::class, 'bulkGenerate'])->middleware('throttle:10,1')->name('monev.bulk-generate');
    Route::post('monev/bulk-reset', [AdminKelasMonevController::class, 'bulkReset'])->middleware('throttle:20,1')->name('monev.bulk-reset');
    Route::get('monev/generation/{generation}/status', [AdminKelasMonevController::class, 'generationStatus'])->name('monev.generation.status');
    Route::get('monev/{anak}/pdf', [AdminKelasMonevController::class, 'exportPdf'])->name('monev.export-pdf');
    Route::get('monev/{anak}', [AdminKelasMonevController::class, 'show'])->name('monev.show');
});

// ─────────────────────────────────────────────
// PENGAJAR
// ─────────────────────────────────────────────
Route::middleware(['auth', 'tenant.context', 'sekolah.active', 'role:Pengajar|Wali Kelas'])->prefix('pengajar')->name('pengajar.')->group(function () {
    Route::get('presensi', [PengajarPresensiController::class, 'index'])->name('presensi.index');
    Route::post('presensi', [PengajarPresensiController::class, 'store'])->name('presensi.store');
    Route::get('presensi-guru', [PengajarPresensiGuruController::class, 'index'])->name('presensi-guru.index');
    Route::post('presensi-guru/check-in', [PengajarPresensiGuruController::class, 'checkIn'])->name('presensi-guru.check-in');
    Route::post('presensi-guru/check-out', [PengajarPresensiGuruController::class, 'checkOut'])->name('presensi-guru.check-out');
    Route::resource('kegiatan', PengajarKegiatanController::class)->except(['create', 'edit', 'show']);
    Route::get('kegiatan/photos/download', [PengajarKegiatanController::class, 'downloadPhotos'])->name('kegiatan.photos.download');
    Route::get('kegiatan/{kegiatan}/photos/download', [PengajarKegiatanController::class, 'downloadPhotosSingle'])->name('kegiatan.photos.download-single');
    Route::resource('matrikulasi', MatrikulasiController::class)->only(['index']);
    Route::post('pencapaian/sync', [PencapaianController::class, 'sync'])->name('pencapaian.sync');
    Route::delete('pencapaian/bundle', [PencapaianController::class, 'destroyBundle'])->name('pencapaian.destroy-bundle');
    Route::get('pencapaian/photos/download', [PencapaianController::class, 'downloadPhotos'])->name('pencapaian.photos.download');
    Route::get('pencapaian/photos/download-bundle', [PencapaianController::class, 'downloadBundlePhoto'])->name('pencapaian.photos.download-bundle');
    Route::resource('pencapaian', PencapaianController::class)->only(['index', 'destroy']);
    Route::post('master-kegiatan-rutin/{master_kegiatan_rutin}/store-rutin', [App\Http\Controllers\Pengajar\MasterKegiatanRutinController::class, 'storeRutin'])->name('master-kegiatan-rutin.store-rutin');
    Route::get('master-kegiatan-rutin/detail/{master_kegiatan_rutin}/{anak}', [App\Http\Controllers\Pengajar\MasterKegiatanRutinController::class, 'detail'])->name('master-kegiatan-rutin.detail');
    Route::delete('master-kegiatan-rutin/rutin/{kegiatan_rutin}', [App\Http\Controllers\Pengajar\MasterKegiatanRutinController::class, 'destroyRutinRecord'])->name('master-kegiatan-rutin.destroy-rutin');
    Route::resource('master-kegiatan-rutin', App\Http\Controllers\Pengajar\MasterKegiatanRutinController::class);
    Route::get('kegiatan-rutin', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'index'])->name('kegiatan-rutin.index');
    Route::post('kegiatan-rutin', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'store'])->name('kegiatan-rutin.store');
    Route::get('kegiatan-rutin/photos/download', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'downloadPhotos'])->name('kegiatan-rutin.photos.download');
    Route::get('kegiatan-rutin/{kegiatan_rutin}/photo/download', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'downloadPhoto'])->name('kegiatan-rutin.photo.download');
    Route::get('kegiatan-rutin/detail/{anak}', [App\Http\Controllers\Pengajar\KegiatanRutinController::class, 'detail'])->name('kegiatan-rutin.detail');
    // AI Feedback Suggestions (web route, uses web session auth)
    Route::post('ai/feedback-suggestions', [AiFeedbackController::class, 'suggest'])->name('ai.feedback-suggestions');
    Route::get('monev-guru', [PengajarMonevGuruController::class, 'index'])->name('monev-guru.index');
    Route::get('monev-guru/{monev_guru_evaluasi}', [PengajarMonevGuruController::class, 'show'])->name('monev-guru.show');
});

// ─────────────────────────────────────────────
// ORANG TUA
// ─────────────────────────────────────────────
Route::middleware(['auth', 'tenant.context', 'sekolah.active', 'role:Orang Tua'])->prefix('orangtua')->name('orangtua.')->group(function () {
    Route::post('active-sekolah', [OrangTuaSchoolSwitcherController::class, 'update'])->name('active-sekolah.update');
    Route::get('anak/tambah', [OrangTuaAnakController::class, 'create'])->name('anak.create');
    Route::post('anak', [OrangTuaAnakController::class, 'store'])->name('anak.store');
    Route::get('kegiatan', [OrangTuaKegiatanController::class, 'index'])->name('kegiatan.index');
    Route::get('kegiatan-rutin', [App\Http\Controllers\OrangTua\KegiatanRutinController::class, 'index'])->name('kegiatan-rutin.index');
    Route::get('pencapaian', [OrangTuaPencapaianController::class, 'index'])->name('pencapaian.index');
    Route::get('pencapaian/photos/download-bundle', [OrangTuaPencapaianController::class, 'downloadBundlePhoto'])->name('pencapaian.photos.download-bundle');
    Route::get('monev', [MonevController::class, 'index'])->name('monev.index');
    Route::get('monev/{anak}/pdf', [MonevController::class, 'exportPdf'])->name('monev.export-pdf');
    Route::get('monev/{anak}', [MonevController::class, 'show'])->name('monev.show');
    Route::get('menu-makanan', [OrangTuaMenuMakananController::class, 'index'])->name('menu-makanan.index');
    Route::get('menu-makanan/{menu_makanan}/photo/download', [OrangTuaMenuMakananController::class, 'downloadPhoto'])->name('menu-makanan.photo.download');
    Route::redirect('kritik-saran/riwayat', '/orangtua/kritik-saran');
    Route::get('kritik-saran', [OrangTuaKritikSaranController::class, 'index'])->name('kritik-saran.index');
    Route::get('kritik-saran/{kritik_saran}', [OrangTuaKritikSaranController::class, 'show'])->name('kritik-saran.show');
    Route::get('kritik-saran/{kritik_saran}/photo/download', [OrangTuaKritikSaranController::class, 'downloadPhoto'])->name('kritik-saran.photo.download');
    Route::post('kritik-saran', [OrangTuaKritikSaranController::class, 'store'])->name('kritik-saran.store');
    Route::patch('kritik-saran/{kritik_saran}', [OrangTuaKritikSaranController::class, 'update'])->name('kritik-saran.update');
    Route::delete('kritik-saran/{kritik_saran}', [OrangTuaKritikSaranController::class, 'destroy'])->name('kritik-saran.destroy');
    Route::get('kesehatan', [App\Http\Controllers\OrangTua\KesehatanController::class, 'index'])->name('kesehatan.index');
    Route::get('presensi', [App\Http\Controllers\OrangTua\PresensiController::class, 'index'])->name('presensi.index');
    Route::post('menu-makanan/vote', [MenuMakananVoteController::class, 'vote'])->name('menu-makanan.vote');
    Route::post('pengumuman/{pengumuman}/baca', [OrangTuaPengumumanController::class, 'markAsRead'])->name('pengumuman.baca');
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat/messages', [ChatController::class, 'store'])->middleware('throttle:20,1')->name('chat.messages.store');
    Route::delete('chat', [ChatController::class, 'destroy'])->name('chat.destroy');

    Route::get('pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('pembayaran/{pembayaran}/invoice', [PembayaranController::class, 'exportInvoice'])->name('pembayaran.invoice');
    Route::get('pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');
    Route::get('pembayaran/{pembayaran}/bukti/download', [PembayaranController::class, 'downloadBukti'])->name('pembayaran.bukti.download');
    Route::post('pembayaran/{pembayaran}/bayar', [PembayaranController::class, 'bayar'])->name('pembayaran.bayar');
});

require __DIR__.'/auth.php';
