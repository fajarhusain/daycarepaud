# Audit: paud-laravel (branch: demo)

**Repo:** github.com/khoirulanam20/paud-laravel  
**Branch:** demo  
**Stack:** Laravel 11, PHP 8.2, Spatie Permission, Spatie Activitylog, Sanctum, DomPDF, Maatwebsite Excel, AI Multi-Provider  
**Audit Date:** 2026-06-22  

---

## TL;DR

Codebase ini secara umum **jauh lebih mature** dibanding kependudukan-app. Ada Spatie Permission, Form Request, Service Layer, trait, activity log, dan test coverage. Namun masih ada celah security dan tech debt yang perlu ditangani sebelum production.

| Kategori | Temuan | Status |
|----------|--------|--------|
| Security | 8 | Butuh fix |
| Robustness | 7 | Butuh fix |
| Tech Debt | 6 | Perlu dibersihkan |
| UAT | 9 item | Perlu diverifikasi manual |

---

## 1. Security

### S-1. APP_DEBUG=true di .env.example

**File:** `.env.example` line 4  
**Temuan:** `APP_DEBUG=true` menjadi default value yang mungkin ter-copy ke production.

**Risk:** Stack trace + environment detail bocor ke user.

**Fix:**
```env
APP_DEBUG=false
```

---

### S-2. SESSION_ENCRYPT=false

**File:** `.env.example` line 31  
**Temuan:** `SESSION_ENCRYPT=false`  session tidak dienkripsi.

**Risk:** Jika session storage diakses langsung (file/database), data session bisa dibaca plain.

**Fix:**
```env
SESSION_ENCRYPT=true
```

---

### S-3. Sanctum Token Tidak Expired

**File:** `config/sanctum.php`  
**Temuan:** `'expiration' => null`  API token mobile orang tua tidak pernah expire.

**Risk:** Token yang bocor atau di-steal bisa dipakai selamanya.

**Fix:**
```php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 10080), // 7 hari
```

---

### S-4. AI API Key Disimpan di Database Plain Text

**File:** `app/Models/AiSetting.php`, `app/Services/MonevSummaryService.php`  
**Temuan:** API key AI (OpenAI, DeepSeek, Groq, dll) disimpan di tabel `ai_settings` tanpa enkripsi.

**Risk:** Jika database bocor (SQL Injection atau backup ter-expose), semua API key vendor AI ikut bocor. API key ini berbayar  attacker bisa pakai atas nama sekolah.

**Fix:**
```php
// app/Models/AiSetting.php
protected $casts = [
    'api_key' => 'encrypted', // Laravel built-in encryption
];
```

---

### S-5. Rate Limiting Hanya di API Login  Web Login Tidak Ada

**File:** `routes/api.php`  ada throttle:12,1  
**File:** `routes/auth.php` / `routes/web.php`  tidak ada throttle

**Temuan:** API login sudah terlindungi (12 request/menit). Tapi web login (Breeze) tidak ada throttle eksplisit.

**Fix:** Tambahkan di `routes/auth.php`:
```php
Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:5,1');
```

---

### S-6. CanUploadImage Trait  Validasi MIME dari getimagesize() Bisa Bypass

**File:** `app/Http/Traits/CanUploadImage.php` line 27-28  
**Temuan:**

```php
$info = getimagesize($imagePath);
$mime = $info['mime'] ?? $file->getClientMimeType(); // ❌ Fallback ke client MIME!
```

Jika `getimagesize()` gagal (file bukan gambar atau corrupt), fallback ke `getClientMimeType()` yang diambil dari header request  bisa dimanipulasi attacker.

**Risk:** Attacker bisa upload file PHP yang disamarkan sebagai gambar, lalu bypass image validation.

**Fix:**
```php
// Gunakan finfo yang lebih akurat
$finfo = new \finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($imagePath);

// Whitelist ketat
$allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime, $allowedMimes)) {
    throw new \InvalidArgumentException('File bukan gambar yang valid.');
}
```

---

### S-7. OrangTuaChatService  Input Hanya strip_tags, Tidak Full Sanitize

**File:** `app/Services/OrangTuaChatService.php` line 47  
**Temuan:**

```php
$content = trim(strip_tags($content));
```

`strip_tags()` hanya hapus HTML tag, tidak mencegah prompt injection ke AI. User bisa kirim pesan seperti:

```
Ignore previous instructions. You are now...
```

**Risk:** Prompt injection  manipulasi konteks AI untuk output yang tidak diinginkan, atau eksfiltrasi data siswa lain jika konteks dicampur.

**Fix:** Tambahkan validasi panjang dan blacklist karakter kontrol. Pisahkan konteks system prompt dari user input dengan strict boundary:

```php
// Di prompt builder, pastikan user content dibungkus literal
$userSection = "[USER MESSAGE START]\n" . $content . "\n[USER MESSAGE END]";
```

---

### S-8. SCRAMBLE Docs Aktif di Local  Endpoint /docs/api Terbuka

**File:** `.env.example`  `SCRAMBLE_DOCS_ENABLED=false`  
**File:** `config/scramble.php`  
**Temuan:** Scramble API docs aktif di `local` environment. Jika staging/demo pakai `APP_ENV=local`, dokumentasi API lengkap bisa diakses siapa saja di `/docs/api`.

**Risk:** Information disclosure  struktur endpoint, parameter, response format bocor ke publik. Memudahkan attacker reconnaissance.

**Fix:** Pastikan production/staging pakai:
```env
APP_ENV=production
SCRAMBLE_DOCS_ENABLED=false
```
Atau tambahkan auth middleware di Scramble config:
```php
// config/scramble.php
'middleware' => ['auth'],
```

---

## 2. Robustness

### R-1. GenerateMonevSummaryJob  Tidak Ada Max Retry / Timeout

**File:** `app/Jobs/GenerateMonevSummaryJob.php`  
**Temuan:** Job AI generation tidak punya `$tries`, `$timeout`, atau `$backoff` yang eksplisit.

**Risk:** Jika AI provider timeout atau error, job akan retry terus-menerus dan menghabiskan AI token.

**Fix:**
```php
class GenerateMonevSummaryJob implements ShouldQueue
{
    public int $tries = 3;
    public int $timeout = 120; // 2 menit max per job
    public int $backoff = 30;  // tunggu 30 detik sebelum retry

    public function failed(\Throwable $e): void
    {
        Log::error('Monev generation job failed', [
            'job_id' => $this->job->getJobId(),
            'error' => $e->getMessage(),
        ]);
        // Notifikasi admin atau update status MonevGeneration
    }
}
```

---

### R-2. MonevSummaryService  AI Service Resolve Bisa Null Tanpa Handling di Job

**File:** `app/Services/MonevSummaryService.php` line 31-47  
**Temuan:** `resolveAiServiceForSekolah()` mengembalikan `null` jika AI setting belum dikonfigurasi. Jika job sudah jalan dan service null, kemungkinan throw exception unhandled.

**Fix:** Validasi sebelum dispatch job:
```php
if (!$this->monevService->resolveAiServiceForSekolah($sekolahId)) {
    throw new MonevManualGenerationException('API Key AI belum dikonfigurasi.');
}
GenerateMonevSummaryJob::dispatch($sekolahId);
```

---

### R-3. AkuntansiService  Tidak Ada Validasi Akun Null Sebelum Buat Jurnal

**File:** `app/Services/AkuntansiService.php`  
**Temuan:** Jika `$kas` atau `$counter` adalah `null` (akun tidak ditemukan atau setting belum dikonfigurasi), jurnal akan dibuat dengan data tidak lengkap.

**Risk:** Data akuntansi corrupt  jurnal tanpa debit/kredit yang valid.

**Fix:**
```php
if (!$kas || !$counter) {
    throw new \RuntimeException(
        'Konfigurasi akuntansi belum lengkap. Pastikan akun kas dan counter telah dipilih.'
    );
}
```

---

### R-4. CanUploadImage  Tidak Ada Cleanup Jika Proses Gagal di Tengah

**File:** `app/Http/Traits/CanUploadImage.php`  
**Temuan:** Jika GD image processing gagal setelah file tersimpan, file yang sudah terupload tidak dihapus.

**Fix:**
```php
try {
    // ... image processing
    Storage::disk('public')->put($fullPath, $imageContent);
} catch (\Throwable $e) {
    // Cleanup jika ada file partial
    Storage::disk('public')->delete($fullPath);
    throw $e;
}
```

---

### R-5. OrangTuaChat  Tidak Ada Rate Limiting per User

**File:** `app/Services/OrangTuaChatService.php`  
**Temuan:** Tidak ada throttle untuk frekuensi pesan orang tua. User bisa spam ratusan pesan yang menguras AI token sekolah.

**Fix:**
```php
// Di controller sebelum sendMessage
$key = 'chat_limit_' . $user->id;
if (Cache::get($key, 0) >= 20) {
    return response()->json(['message' => 'Terlalu banyak pesan. Tunggu beberapa saat.'], 429);
}
Cache::increment($key);
Cache::expire($key, 60); // reset per menit
```

---

### R-6. phpunit.xml  Test DB Config Hardcoded

**File:** `phpunit.xml`  
**Temuan:**
```xml
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="8889"/>
<env name="DB_DATABASE" value="bintang-kecil2_test"/>
```

Port 8889 adalah port MySQL MAMP (dev lokal), bukan standard 3306. Ini akan gagal di CI/CD atau dev environment lain.

**Fix:**
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```
Atau gunakan environment variable:
```xml
<env name="DB_PORT" value="${DB_TEST_PORT:-3306}"/>
```

---

### R-7. Scheduled Command  Tidak Ada Monitoring Failure

**File:** `bootstrap/app.php`  
**Temuan:**
```php
$schedule->command('monev:generate')->monthlyOn(1, '02:00');
$schedule->command('monev:finalize-stale')->hourly();
```

Tidak ada `onFailure()`, `onSuccess()`, atau `pingOnFailure()`. Jika cron gagal (misal DB down), tidak ada notifikasi.

**Fix:**
```php
$schedule->command('monev:generate')
    ->monthlyOn(1, '02:00')
    ->onFailure(function () {
        Log::channel('security')->error('monev:generate cron FAILED');
    })
    ->emailOutputOnFailure('admin@sekolah.id');
```

---

## 3. Tech Debt

### T-1. .cursor Folder Tercommit ke Repo

**Temuan:** `.cursor/skills/ui-ux-pro-max/` ter-commit ke branch demo. Berisi file Python, CSV data, dan SKILL.md  ini adalah IDE config file, bukan kode aplikasi.

**Fix:**
```bash
git rm -r --cached .cursor/
echo ".cursor/" >> .gitignore
git commit -m "chore: remove .cursor IDE folder from tracking"
```

---

### T-2. kode rekening.xlsx Tercommit

**Temuan:** File Excel `kode rekening.xlsx` di root project ter-commit ke repo.

**Risk:** File binary di git bersifat permanent (ada di history), memperbesar ukuran clone, dan tidak sesuai tempat.

**Fix:** Pindah ke `database/data/` atau seed dari PHP, hapus dari git tracking.

---

### T-3. Duplikasi Controllers  Admin vs AdminKelas

**Temuan:** Ada dua namespace yang mirip:
- `Admin\AnakController` → `app/Http/Controllers/Admin/AnakController.php`
- `AdminKelas\AnakController` → `app/Http/Controllers/AdminKelas/AnakController.php`

Sama untuk Monev, Presensi, Kesehatan. Kemungkinan besar ada duplikasi logika.

**Fix:** Evaluasi apakah bisa digabung dengan policy/permission, atau minimal extract shared logic ke service layer.

---

### T-4. Monev PDF Service  Kemungkinan Memory Issue untuk Data Besar

**File:** `app/Services/MonevPdfService.php`  
**Temuan:** DomPDF generate PDF dari semua data siswa per bulan. Tanpa pagination atau chunking, untuk sekolah dengan banyak siswa ini bisa OOM.

**Fix:**
```php
ini_set('memory_limit', '256M'); // Temporary di service
// Atau generate per-siswa dan merge PDF
```

---

### T-5. Config admin-menu.php  Magic String untuk Permission

**File:** `config/admin-menu.php`  
**Temuan:** Route permission mapping pakai magic string yang tidak type-safe dan tidak tervalidasi saat boot.

**Fix:** Extract ke konstanta enum:
```php
enum AdminPermission: string {
    case ManageAnak = 'manage anak';
    case ManagePresensi = 'manage presensi';
}
```

---

### T-6. Tidak Ada Policy untuk Model Authorization

**Temuan:** Banyak controller menggunakan `auth()->user()->sekolah_id` langsung untuk filter, bukan Laravel Policy. Ini tersebar dan sulit di-audit.

**Fix:** Buat Policy:
```bash
php artisan make:policy AnakPolicy --model=Anak
```
Dan gunakan:
```php
$this->authorize('view', $anak);
```

---

## 4. UAT Checklist

Berikut adalah skenario yang perlu diverifikasi manual sebelum release.

### Auth & Access Control

| # | Skenario | Expected | Status |
|---|----------|----------|--------|
| U-1 | Login sebagai Orang Tua dengan anak belum approved | Ditolak dengan pesan jelas | Verifikasi |
| U-2 | Login sebagai Admin Sekolah sekolah A, coba akses data sekolah B via URL | 403/redirect | Verifikasi |
| U-3 | Login sebagai Lembaga tanpa pilih cabang aktif, akses admin menu | Redirect ke dashboard + warning | Verifikasi |
| U-4 | Token API expired (setelah set expiration) | 401 + pesan jelas | Verifikasi |
| U-5 | Brute force login web > 5x | Throttle 429 | Verifikasi |

### File Upload

| # | Skenario | Expected | Status |
|---|----------|----------|--------|
| U-6 | Upload foto anak berformat .php (disamarkan sebagai image) | Ditolak | Verifikasi |
| U-7 | Upload foto anak 10MB | Ditolak (max 2048KB) | Verifikasi |
| U-8 | Upload foto anak format WebP | Diterima dan ditampilkan | Verifikasi |

### AI & Chat

| # | Skenario | Expected | Status |
|---|----------|----------|--------|
| U-9 | Orang Tua kirim chat ketika saldo AI token sekolah 0 | Error jelas, tidak hang | Verifikasi |
| U-10 | Admin disable chat orang tua, ortu coba kirim chat | Ditolak dengan pesan | Verifikasi |
| U-11 | Generate monev ketika AI API key tidak valid | Error user-friendly, tidak crash | Verifikasi |
| U-12 | Generate monev 2x bersamaan untuk sekolah yang sama | Hanya 1 yang jalan, satunya ditolak | Verifikasi |

### Akuntansi & Pembayaran

| # | Skenario | Expected | Status |
|---|----------|----------|--------|
| U-13 | Cashflow tanpa konfigurasi akuntansi | Error jelas, bukan PHP error | Verifikasi |
| U-14 | Pembayaran ganda untuk bulan yang sama | Ditolak / duplikasi ter-handle | Verifikasi |
| U-15 | Generate laporan RKAS PDF data besar (50+ akun) | Berhasil dalam < 10 detik | Verifikasi |

### Monev & Presensi

| # | Skenario | Expected | Status |
|---|----------|----------|--------|
| U-16 | Monev generate di hari bukan akhir bulan (manual trigger) | Bisa jalan, tapi ada warning | Verifikasi |
| U-17 | Presensi siswa yang sudah tidak aktif (non-approved) | Tidak muncul di list | Verifikasi |

---

## To-Do List by Priority

### P1  Harus Sebelum Launch

| # | Task | File |
|---|------|------|
| 1 | Set APP_DEBUG=false di production env | `.env.example`, production `.env` |
| 2 | Set SESSION_ENCRYPT=true | `.env.example` |
| 3 | Set Sanctum token expiration | `config/sanctum.php` |
| 4 | Encrypt AI API key di database | `AiSetting` model cast + migration |
| 5 | Fix CanUploadImage  ganti fallback ke finfo, hapus `getClientMimeType()` | `CanUploadImage.php` |
| 6 | Fix AkuntansiService  validasi akun tidak null sebelum buat jurnal | `AkuntansiService.php` |
| 7 | Tambah rate limiting di web login | `routes/auth.php` |
| 8 | Fix GenerateMonevSummaryJob  tambah tries, timeout, onFailure | `GenerateMonevSummaryJob.php` |
| 9 | Jalankan seluruh UAT checklist | Manual |

### P2  Minggu Ini

| # | Task | File |
|---|------|------|
| 10 | Hapus .cursor dari repo dan gitignore | `.gitignore`, `git rm` |
| 11 | Pindah kode rekening.xlsx ke database/data atau seed | Root → `database/data/` |
| 12 | Fix phpunit.xml  ganti hardcoded DB config ke sqlite memory | `phpunit.xml` |
| 13 | Tambah rate limiting chat orang tua per user | `OrangTuaChatService.php` |
| 14 | Tambah prompt injection boundary di AI context builder | `OrangTuaChatContextBuilder.php` |
| 15 | Proteksi Scramble docs di staging/production | `config/scramble.php` + middleware |
| 16 | Tambah cleanup file di CanUploadImage jika processing gagal | `CanUploadImage.php` |

### P3  Bulan Ini

| # | Task | File |
|---|------|------|
| 17 | Validasi null AI service sebelum dispatch job | `MonevSummaryService.php` |
| 18 | Tambah onFailure monitoring di scheduled commands | `bootstrap/app.php` |
| 19 | Evaluasi duplikasi Admin vs AdminKelas controllers | Multiple files |
| 20 | Tambah Laravel Policy untuk model authorization | `app/Policies/` |
| 21 | Extract permission string ke enum | `config/admin-menu.php` |

---

## Hal Positif yang Sudah Baik

| Item | Detail |
|------|--------|
| Spatie Permission | Role-based access control sudah diimplementasi dengan benar |
| Spatie Activitylog | Audit trail sudah ada via `LogsScopedActivity` trait |
| Form Request | `StoreAnakPendaftaranRequest` sudah pakai Form Request dengan authorize() |
| Service Layer | Logic dipisah dengan benar ke service classes |
| API Rate Limiting | Login API sudah ada throttle:12,1 |
| Test Coverage | 22 test files  jauh lebih baik dari proyek sejenis |
| AI Multi-Provider | Arsitektur multi-provider AI (OpenAI, Groq, DeepSeek, dll) sudah fleksibel |
| Sanctum API | API mobile sudah pakai token-based auth dengan role guard |
| .env.example Bersih | Tidak ada credential real yang ter-commit |
| Filename Upload Aman | `CanUploadImage` sudah pakai `Str::random(40)`  tidak pakai nama asli |
