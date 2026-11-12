<?php

namespace App\Exceptions;

final class ApiErrorCode
{
    public const PERMINTAAN_TIDAK_VALID = 'PERMINTAAN_TIDAK_VALID';

    public const SESI_BERAKHIR = 'SESI_BERAKHIR';

    public const TOKEN_TIDAK_VALID = 'TOKEN_TIDAK_VALID';

    public const KREDENSIAL_SALAH = 'KREDENSIAL_SALAH';

    public const AKSES_DITOLAK = 'AKSES_DITOLAK';

    public const AKUN_TIDAK_DAPAT_DIGUNAKAN = 'AKUN_TIDAK_DAPAT_DIGUNAKAN';

    public const AKUN_BELUM_AKTIF = 'AKUN_BELUM_AKTIF';

    public const SEKOLAH_TIDAK_AKTIF = 'SEKOLAH_TIDAK_AKTIF';

    public const FITUR_NONAKTIF = 'FITUR_NONAKTIF';

    public const DATA_TIDAK_DITEMUKAN = 'DATA_TIDAK_DITEMUKAN';

    public const ANAK_TIDAK_DITEMUKAN = 'ANAK_TIDAK_DITEMUKAN';

    public const METODE_TIDAK_DIDUKUNG = 'METODE_TIDAK_DIDUKUNG';

    public const KONFLIK_DATA = 'KONFLIK_DATA';

    public const IDEMPOTENCY_KEY_BERBEDA = 'IDEMPOTENCY_KEY_BERBEDA';

    public const PERMINTAAN_SEDANG_DIPROSES = 'PERMINTAAN_SEDANG_DIPROSES';

    public const TAUTAN_KEDALUWARSA = 'TAUTAN_KEDALUWARSA';

    public const FILE_TERLALU_BESAR = 'FILE_TERLALU_BESAR';

    public const FORMAT_FILE_TIDAK_DIDUKUNG = 'FORMAT_FILE_TIDAK_DIDUKUNG';

    public const VALIDASI_GAGAL = 'VALIDASI_GAGAL';

    public const TERLALU_BANYAK_PERMINTAAN = 'TERLALU_BANYAK_PERMINTAAN';

    public const KESALAHAN_SERVER = 'KESALAHAN_SERVER';

    public const LAYANAN_TIDAK_TERSEDIA = 'LAYANAN_TIDAK_TERSEDIA';

    public const GOOGLE_EMAIL_TIDAK_TERVERIFIKASI = 'GOOGLE_EMAIL_TIDAK_TERVERIFIKASI';

    public const GOOGLE_TOKEN_TIDAK_VALID = 'GOOGLE_TOKEN_TIDAK_VALID';

    public const ROLE_TIDAK_SESUAI = 'ROLE_TIDAK_SESUAI';

    public const SEKOLAH_TIDAK_DITEMUKAN = 'SEKOLAH_TIDAK_DITEMUKAN';

    public const LAPORAN_BELUM_TERSEDIA = 'LAPORAN_BELUM_TERSEDIA';

    public const PDF_TIDAK_TERSEDIA = 'PDF_TIDAK_TERSEDIA';

    public const INVOICE_BELUM_TERSEDIA = 'INVOICE_BELUM_TERSEDIA';

    public const TAGIHAN_SUDAH_DIPROSES = 'TAGIHAN_SUDAH_DIPROSES';

    public const TAGIHAN_SUDAH_LUNAS = 'TAGIHAN_SUDAH_LUNAS';

    public const BUKTI_PEMBAYARAN_TIDAK_VALID = 'BUKTI_PEMBAYARAN_TIDAK_VALID';

    public const MASUKAN_SUDAH_DIPROSES = 'MASUKAN_SUDAH_DIPROSES';

    public const VOTE_TIDAK_VALID = 'VOTE_TIDAK_VALID';

    public const PENGUMUMAN_TIDAK_TERSEDIA = 'PENGUMUMAN_TIDAK_TERSEDIA';

    public const PENDAFTARAN_DUPLIKAT = 'PENDAFTARAN_DUPLIKAT';

    public const PENDAFTARAN_DITUTUP = 'PENDAFTARAN_DITUTUP';

    public const CHAT_NONAKTIF = 'CHAT_NONAKTIF';

    public const KUOTA_AI_HABIS = 'KUOTA_AI_HABIS';

    public const AI_TIDAK_TERSEDIA = 'AI_TIDAK_TERSEDIA';

    /**
     * @var array<string, int>
     */
    private const STATUSES = [
        self::PERMINTAAN_TIDAK_VALID => 400,
        self::SESI_BERAKHIR => 401,
        self::TOKEN_TIDAK_VALID => 401,
        self::KREDENSIAL_SALAH => 401,
        self::AKSES_DITOLAK => 403,
        self::AKUN_TIDAK_DAPAT_DIGUNAKAN => 403,
        self::AKUN_BELUM_AKTIF => 403,
        self::SEKOLAH_TIDAK_AKTIF => 403,
        self::FITUR_NONAKTIF => 403,
        self::DATA_TIDAK_DITEMUKAN => 404,
        self::ANAK_TIDAK_DITEMUKAN => 404,
        self::METODE_TIDAK_DIDUKUNG => 405,
        self::KONFLIK_DATA => 409,
        self::IDEMPOTENCY_KEY_BERBEDA => 409,
        self::PERMINTAAN_SEDANG_DIPROSES => 409,
        self::TAUTAN_KEDALUWARSA => 410,
        self::FILE_TERLALU_BESAR => 413,
        self::FORMAT_FILE_TIDAK_DIDUKUNG => 415,
        self::VALIDASI_GAGAL => 422,
        self::TERLALU_BANYAK_PERMINTAAN => 429,
        self::KESALAHAN_SERVER => 500,
        self::LAYANAN_TIDAK_TERSEDIA => 503,
        self::GOOGLE_EMAIL_TIDAK_TERVERIFIKASI => 403,
        self::GOOGLE_TOKEN_TIDAK_VALID => 403,
        self::ROLE_TIDAK_SESUAI => 403,
        self::SEKOLAH_TIDAK_DITEMUKAN => 404,
        self::LAPORAN_BELUM_TERSEDIA => 404,
        self::PDF_TIDAK_TERSEDIA => 404,
        self::INVOICE_BELUM_TERSEDIA => 403,
        self::TAGIHAN_SUDAH_DIPROSES => 409,
        self::TAGIHAN_SUDAH_LUNAS => 409,
        self::BUKTI_PEMBAYARAN_TIDAK_VALID => 422,
        self::MASUKAN_SUDAH_DIPROSES => 409,
        self::VOTE_TIDAK_VALID => 422,
        self::PENGUMUMAN_TIDAK_TERSEDIA => 403,
        self::PENDAFTARAN_DUPLIKAT => 409,
        self::PENDAFTARAN_DITUTUP => 403,
        self::CHAT_NONAKTIF => 422,
        self::KUOTA_AI_HABIS => 422,
        self::AI_TIDAK_TERSEDIA => 503,
    ];

    public static function status(string $code): int
    {
        return self::STATUSES[$code] ?? 500;
    }

    public static function message(string $code): string
    {
        $key = 'api.errors.'.$code;
        $message = trans($key, [], 'id');

        return $message === $key
            ? 'Terjadi kendala di server. Silakan coba lagi beberapa saat lagi.'
            : $message;
    }
}
