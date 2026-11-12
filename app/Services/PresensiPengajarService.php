<?php

namespace App\Services;

use App\Models\Pengajar;
use App\Models\PresensiPengajar;
use App\Models\PresensiShift;
use App\Models\Sekolah;
use App\Models\SekolahHariLibur;
use App\Models\SekolahJamKerja;
use App\Support\GeoCoordinate;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Logika presensi guru: geofence (Haversine), status telat masuk/pulang,
 * dan rekap kehadiran. Method rekap sengaja netral supaya bisa dipakai
 * monev guru tanpa mengubah modul ini.
 */
class PresensiPengajarService
{
    /** Akurasi GPS maksimum yang diterima (meter). */
    public const AKURASI_MAKS_METER = 200;

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function koordinatSekolah(Sekolah $sekolah): ?array
    {
        $default = $sekolah->relationLoaded('defaultPresensiShift')
            ? $sekolah->defaultPresensiShift
            : PresensiShift::withoutSekolahScope()
                ->where('sekolah_id', $sekolah->id)
                ->where('is_default', true)
                ->first();

        if ($default !== null) {
            $parsed = $this->koordinatShift($default);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return GeoCoordinate::parse($sekolah->location_coordinate);
    }

    /**
     * @return array{lat: float, lng: float}|null
     */
    public function koordinatShift(PresensiShift $shift): ?array
    {
        return GeoCoordinate::parse($shift->location_coordinate);
    }

    public function resolveShift(Pengajar $pengajar): PresensiShift
    {
        if ($pengajar->presensi_shift_id === null) {
            throw ValidationException::withMessages([
                'presensi' => 'Shift presensi belum diatur untuk akun Anda. Hubungi admin sekolah.',
            ]);
        }

        $shift = PresensiShift::withoutSekolahScope()
            ->where('sekolah_id', $pengajar->sekolah_id)
            ->whereKey($pengajar->presensi_shift_id)
            ->first();

        if ($shift === null) {
            throw ValidationException::withMessages([
                'presensi' => 'Shift presensi tidak valid. Hubungi admin sekolah.',
            ]);
        }

        return $shift;
    }

    public function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function jadwalHari(PresensiShift $shift, CarbonInterface $tanggal): ?SekolahJamKerja
    {
        return SekolahJamKerja::withoutSekolahScope()
            ->where('presensi_shift_id', $shift->id)
            ->where('hari', $tanggal->dayOfWeekIso)
            ->first();
    }

    public function isLibur(Sekolah $sekolah, PresensiShift $shift, CarbonInterface $tanggal): bool
    {
        if ($sekolah->hariLiburs()
            ->whereDate('tanggal', $tanggal->toDateString())
            ->exists()) {
            return true;
        }

        $jadwal = $this->jadwalHari($shift, $tanggal);

        return $jadwal !== null && ! $jadwal->is_kerja;
    }

    /** Jam pulang efektif hari itu, atau null kalau hari libur / belum diatur. */
    public function jamPulangHari(PresensiShift $shift, CarbonInterface $tanggal): ?CarbonInterface
    {
        $jadwal = $this->jadwalHari($shift, $tanggal);

        if (! $jadwal || ! $jadwal->is_kerja || ! $jadwal->jam_pulang) {
            return null;
        }

        return Carbon::parse($tanggal->toDateString().' '.$jadwal->jam_pulang);
    }

    /** Check-out hanya dibuka mulai jam pulang. */
    public function bolehCheckOut(PresensiShift $shift, CarbonInterface $tanggal): bool
    {
        $batas = $this->jamPulangHari($shift, $tanggal);

        return $batas === null || $tanggal->gte($batas);
    }

    public function presensiHariIni(Pengajar $pengajar, CarbonInterface $tanggal): ?PresensiPengajar
    {
        return PresensiPengajar::withoutSekolahScope()
            ->where('sekolah_id', $pengajar->sekolah_id)
            ->where('pengajar_id', $pengajar->id)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->first();
    }

    /**
     * Ringkasan kondisi presensi guru untuk hari tertentu (dipakai view).
     *
     * @return array{
     *     tanggal: CarbonInterface,
     *     shift: PresensiShift,
     *     shift_nama: string,
     *     jadwal: SekolahJamKerja|null,
     *     presensi: PresensiPengajar|null,
     *     libur: bool,
     *     koordinat_set: bool,
     *     radius_meter: int,
     *     bisa_check_in: bool,
     *     bisa_check_out: bool
     * }
     */
    public function statusHariIni(Pengajar $pengajar, Sekolah $sekolah, CarbonInterface $tanggal): array
    {
        $shift = $this->resolveShift($pengajar);
        $presensi = $this->presensiHariIni($pengajar, $tanggal);
        $koordinatSet = $this->koordinatShift($shift) !== null;
        $libur = $this->isLibur($sekolah, $shift, $tanggal);

        return [
            'tanggal' => $tanggal,
            'shift' => $shift,
            'shift_nama' => $shift->name,
            'jadwal' => $this->jadwalHari($shift, $tanggal),
            'presensi' => $presensi,
            'libur' => $libur,
            'koordinat_set' => $koordinatSet,
            'radius_meter' => $shift->radiusMeter(),
            'bisa_check_in' => ! $libur && $koordinatSet && $presensi?->check_in_at === null,
            'bisa_check_out' => ! $libur && $koordinatSet
                && $presensi?->check_in_at !== null
                && $presensi?->check_out_at === null
                && $this->bolehCheckOut($shift, $tanggal),
        ];
    }

    /**
     * @param  array{latitude: float, longitude: float, accuracy?: float|null}  $payload
     */
    public function checkIn(Pengajar $pengajar, Sekolah $sekolah, array $payload): PresensiPengajar
    {
        $now = Carbon::now();
        $shift = $this->resolveShift($pengajar);
        $this->pastikanLokasiShift($shift);
        $this->pastikanTidakLibur($sekolah, $shift, $now);
        $this->pastikanAkurasiWajar($payload['accuracy'] ?? null);

        $presensi = $this->presensiHariIni($pengajar, $now);

        if ($presensi?->check_in_at !== null) {
            throw ValidationException::withMessages([
                'presensi' => 'Anda sudah melakukan check-in hari ini.',
            ]);
        }

        $koordinat = $this->koordinatShift($shift);
        $jarak = $this->jarakMeter(
            $payload['latitude'],
            $payload['longitude'],
            $koordinat['lat'],
            $koordinat['lng'],
        );

        if ($jarak > $shift->radiusMeter()) {
            throw ValidationException::withMessages([
                'presensi' => sprintf(
                    'Anda berada di luar area shift %s (%.0f m dari titik absensi, maksimal %d m).',
                    $shift->name,
                    $jarak,
                    $shift->radiusMeter(),
                ),
            ]);
        }

        [$terlambat, $menitTerlambat] = $this->hitungTerlambatMasuk($shift, $now);

        return PresensiPengajar::withoutSekolahScope()->updateOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'pengajar_id' => $pengajar->id,
                'tanggal' => $now->toDateString(),
            ],
            [
                'presensi_shift_id' => $shift->id,
                'hadir' => true,
                'status' => null,
                'check_in_at' => $now,
                'check_in_latitude' => $payload['latitude'],
                'check_in_longitude' => $payload['longitude'],
                'check_in_accuracy' => $payload['accuracy'] ?? null,
                'jarak_meter' => round($jarak, 2),
                'di_luar_area' => false,
                'sumber' => PresensiPengajar::SUMBER_SELF,
                'terlambat_masuk' => $terlambat,
                'menit_telat_masuk' => $menitTerlambat,
            ],
        );
    }

    /**
     * @param  array{latitude: float, longitude: float, accuracy?: float|null}  $payload
     */
    public function checkOut(Pengajar $pengajar, Sekolah $sekolah, array $payload): PresensiPengajar
    {
        $now = Carbon::now();
        $shift = $this->resolveShift($pengajar);
        $this->pastikanLokasiShift($shift);
        $this->pastikanTidakLibur($sekolah, $shift, $now);
        $this->pastikanAkurasiWajar($payload['accuracy'] ?? null);

        $presensi = $this->presensiHariIni($pengajar, $now);

        if ($presensi?->check_in_at === null) {
            throw ValidationException::withMessages([
                'presensi' => 'Anda belum melakukan check-in hari ini.',
            ]);
        }

        if ($presensi->check_out_at !== null) {
            throw ValidationException::withMessages([
                'presensi' => 'Anda sudah melakukan check-out hari ini.',
            ]);
        }

        $batasPulang = $this->jamPulangHari($shift, $now);

        if ($batasPulang !== null && $now->lt($batasPulang)) {
            throw ValidationException::withMessages([
                'presensi' => 'Check-out baru dibuka pukul '.$batasPulang->format('H:i').'.',
            ]);
        }

        $koordinat = $this->koordinatShift($shift);
        $jarak = $this->jarakMeter(
            $payload['latitude'],
            $payload['longitude'],
            $koordinat['lat'],
            $koordinat['lng'],
        );

        if ($jarak > $shift->radiusMeter()) {
            throw ValidationException::withMessages([
                'presensi' => sprintf(
                    'Anda berada di luar area shift %s (%.0f m dari titik absensi, maksimal %d m).',
                    $shift->name,
                    $jarak,
                    $shift->radiusMeter(),
                ),
            ]);
        }

        [$telatPulang, $menitTelatPulang] = $this->hitungTelatPulang($shift, $presensi->check_in_at, $now);

        $presensi->forceFill([
            'check_out_at' => $now,
            'check_out_latitude' => $payload['latitude'],
            'check_out_longitude' => $payload['longitude'],
            'check_out_accuracy' => $payload['accuracy'] ?? null,
            'telat_pulang' => $telatPulang,
            'menit_telat_pulang' => $menitTelatPulang,
        ])->save();

        return $presensi->refresh();
    }

    /**
     * Rekap kehadiran guru pada rentang tanggal. Netral, siap dipakai monev.
     *
     * @return array{
     *     hari_kerja: int, hadir: int, terlambat: int, telat_pulang: int,
     *     tanpa_check_out: int, persentase: float
     * }
     */
    public function rekapKehadiran(Pengajar $pengajar, CarbonInterface $mulai, CarbonInterface $selesai): array
    {
        $mulai = Carbon::parse($mulai)->startOfDay();
        $selesai = Carbon::parse($selesai)->endOfDay();

        $rows = PresensiPengajar::withoutSekolahScope()
            ->where('pengajar_id', $pengajar->id)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->get();

        $hadir = $rows->whereStrict('hadir', true)->count();
        $terlambat = $rows->where('terlambat_masuk', true)->count();
        $telatPulang = $rows->where('telat_pulang', true)->count();
        $tanpaCheckOut = $rows->filter(fn (PresensiPengajar $row) => $row->check_in_at !== null && $row->check_out_at === null)->count();

        $hariKerja = $this->hitungHariKerja($pengajar, $mulai, $selesai);

        return [
            'hari_kerja' => $hariKerja,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'telat_pulang' => $telatPulang,
            'tanpa_check_out' => $tanpaCheckOut,
            'persentase' => $hariKerja > 0 ? round($hadir / $hariKerja * 100, 2) : 0.0,
        ];
    }

    public function hitungHariKerja(Pengajar $pengajar, CarbonInterface $mulai, CarbonInterface $selesai): int
    {
        $mulai = Carbon::parse($mulai)->startOfDay();
        $selesai = Carbon::parse($selesai)->endOfDay();

        $shift = $this->resolveShift($pengajar);

        $jadwalKerja = SekolahJamKerja::withoutSekolahScope()
            ->where('presensi_shift_id', $shift->id)
            ->where('is_kerja', true)
            ->pluck('hari')
            ->all();

        $hariLibur = $this->hariLiburSet($pengajar->sekolah_id, $mulai, $selesai);

        $total = 0;

        for ($tanggal = $mulai->copy(); $tanggal->lte($selesai); $tanggal->addDay()) {
            if (! in_array($tanggal->dayOfWeekIso, $jadwalKerja, true)) {
                continue;
            }

            if (isset($hariLibur[$tanggal->toDateString()])) {
                continue;
            }

            $total++;
        }

        return $total;
    }

    /**
     * @return array<string, true>
     */
    private function hariLiburSet(int $sekolahId, CarbonInterface $mulai, CarbonInterface $selesai): array
    {
        return SekolahHariLibur::withoutSekolahScope()
            ->where('sekolah_id', $sekolahId)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->pluck('tanggal')
            ->mapWithKeys(function ($tanggal): array {
                $value = $tanggal instanceof CarbonInterface ? $tanggal->toDateString() : (string) $tanggal;

                return [$value => true];
            })
            ->all();
    }

    private function pastikanLokasiShift(PresensiShift $shift): void
    {
        if ($this->koordinatShift($shift) === null) {
            throw ValidationException::withMessages([
                'presensi' => 'Lokasi shift '.$shift->name.' belum diatur. Hubungi admin sekolah.',
            ]);
        }
    }

    private function pastikanTidakLibur(Sekolah $sekolah, PresensiShift $shift, CarbonInterface $now): void
    {
        if ($this->isLibur($sekolah, $shift, $now)) {
            throw ValidationException::withMessages([
                'presensi' => 'Hari ini libur, presensi tidak dibuka.',
            ]);
        }
    }

    private function pastikanAkurasiWajar(?float $accuracy): void
    {
        if ($accuracy !== null && $accuracy > self::AKURASI_MAKS_METER) {
            throw ValidationException::withMessages([
                'presensi' => 'Akurasi GPS terlalu rendah. Coba lagi di area terbuka.',
            ]);
        }
    }

    /**
     * @return array{0: bool, 1: int|null}
     */
    public function hitungTerlambatMasuk(PresensiShift $shift, CarbonInterface $now): array
    {
        $jadwal = $this->jadwalHari($shift, $now);

        if (! $jadwal || ! $jadwal->is_kerja || ! $jadwal->jam_masuk) {
            return [false, null];
        }

        $batas = Carbon::parse($now->toDateString().' '.$jadwal->jam_masuk)
            ->addMinutes($jadwal->toleransi_masuk_menit);

        if ($now->lte($batas)) {
            return [false, 0];
        }

        return [true, (int) $batas->diffInMinutes($now)];
    }

    /**
     * @return array{0: bool, 1: int|null}
     */
    public function hitungTelatPulang(PresensiShift $shift, CarbonInterface $checkIn, CarbonInterface $checkOut): array
    {
        $jadwal = $this->jadwalHari($shift, $checkOut);

        if (! $jadwal || ! $jadwal->is_kerja || ! $jadwal->jam_pulang) {
            return [false, null];
        }

        $batas = Carbon::parse($checkOut->toDateString().' '.$jadwal->jam_pulang)
            ->addMinutes($jadwal->toleransi_pulang_menit);

        if ($checkOut->lte($batas)) {
            return [false, 0];
        }

        return [true, (int) $batas->diffInMinutes($checkOut)];
    }
}
