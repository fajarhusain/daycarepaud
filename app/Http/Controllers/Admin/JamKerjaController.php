<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PresensiShift;
use App\Models\Sekolah;
use App\Models\SekolahHariLibur;
use App\Models\SekolahJamKerja;
use App\Support\ActivityLogger;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class JamKerjaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizePengaturanPresensi();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());

        $this->pastikanShiftDanJadwalLengkap($sekolah);

        $shifts = $sekolah->presensiShifts()->get();
        $activeShift = $this->resolveActiveShift($shifts, $request->integer('shift'));

        return view('admin.presensi-setting.index', [
            'sekolah' => $sekolah,
            'shifts' => $shifts,
            'activeShift' => $activeShift,
            'jadwals' => $activeShift->jamKerjas()->get()->keyBy('hari'),
            'hariLiburs' => $sekolah->hariLiburs()->get(),
        ]);
    }

    public function update(Request $request)
    {
        $this->authorizePengaturanPresensi();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());
        $shift = $this->findTenantShift($sekolah, (int) $request->input('presensi_shift_id'));

        $validated = $request->validate([
            'presensi_shift_id' => ['required', 'integer'],
            'radius_m' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'location_coordinate' => [
                'nullable',
                'string',
                'max:100',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! filled($value)) {
                        return;
                    }

                    $raw = preg_replace('/\s+/', '', trim((string) $value)) ?? '';
                    $parts = explode(',', $raw);

                    if (count($parts) !== 2) {
                        $fail('Koordinat lokasi harus berformat "lat,lng".');

                        return;
                    }

                    $lat = filter_var($parts[0], FILTER_VALIDATE_FLOAT);
                    $lng = filter_var($parts[1], FILTER_VALIDATE_FLOAT);

                    if ($lat === false || $lng === false) {
                        $fail('Koordinat lokasi harus berupa angka desimal.');

                        return;
                    }

                    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                        $fail('Koordinat lokasi di luar rentang yang valid.');
                    }
                },
            ],
            'jadwal' => ['required', 'array'],
            'jadwal.*.is_kerja' => ['nullable', 'boolean'],
            'jadwal.*.jam_masuk' => ['nullable', 'date_format:H:i'],
            'jadwal.*.jam_pulang' => ['nullable', 'date_format:H:i'],
            'jadwal.*.toleransi_masuk_menit' => ['nullable', 'integer', 'min:0', 'max:600'],
            'jadwal.*.toleransi_pulang_menit' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $koordinat = null;
        if ($request->has('location_coordinate')) {
            $raw = preg_replace('/\s+/', '', trim((string) $request->input('location_coordinate', ''))) ?? '';
            $koordinat = $raw === '' ? null : $raw;
        }

        $shift->update([
            'radius_m' => $validated['radius_m'] ?? null,
            'location_coordinate' => $koordinat,
        ]);

        foreach ($validated['jadwal'] as $hari => $row) {
            $isKerja = (bool) ($row['is_kerja'] ?? false);

            SekolahJamKerja::withoutSekolahScope()
                ->where('presensi_shift_id', $shift->id)
                ->where('hari', (int) $hari)
                ->update([
                    'is_kerja' => $isKerja,
                    'jam_masuk' => $isKerja ? ($row['jam_masuk'] ?? null) : null,
                    'jam_pulang' => $isKerja ? ($row['jam_pulang'] ?? null) : null,
                    'toleransi_masuk_menit' => (int) ($row['toleransi_masuk_menit'] ?? 0),
                    'toleransi_pulang_menit' => (int) ($row['toleransi_pulang_menit'] ?? 0),
                ]);
        }

        ActivityLogger::log('Pengaturan presensi shift diperbarui', $shift, [
            'shift' => $shift->name,
            'radius_m' => $shift->radius_m,
        ]);

        return redirect()
            ->route('admin.presensi-setting.index', ['shift' => $shift->id])
            ->with('success', 'Pengaturan shift "'.$shift->name.'" berhasil disimpan.');
    }

    public function storeShift(Request $request)
    {
        $this->authorizeKelolaShift();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());
        $this->pastikanShiftDanJadwalLengkap($sekolah);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('presensi_shifts', 'name')->where(fn ($q) => $q->where('sekolah_id', $sekolah->id)),
            ],
        ]);

        $default = $sekolah->presensiShifts()->where('is_default', true)->first()
            ?? $sekolah->presensiShifts()->orderBy('id')->firstOrFail();

        $maxSort = (int) $sekolah->presensiShifts()->max('sort_order');

        $shift = PresensiShift::withoutSekolahScope()->create([
            'sekolah_id' => $sekolah->id,
            'name' => $validated['name'],
            'location_coordinate' => $default->location_coordinate,
            'radius_m' => $default->radius_m,
            'is_default' => false,
            'sort_order' => $maxSort + 1,
        ]);

        foreach ($default->jamKerjas as $row) {
            SekolahJamKerja::withoutSekolahScope()->create([
                'sekolah_id' => $sekolah->id,
                'presensi_shift_id' => $shift->id,
                'hari' => $row->hari,
                'is_kerja' => $row->is_kerja,
                'jam_masuk' => $row->jam_masuk,
                'jam_pulang' => $row->jam_pulang,
                'toleransi_masuk_menit' => $row->toleransi_masuk_menit,
                'toleransi_pulang_menit' => $row->toleransi_pulang_menit,
            ]);
        }

        ActivityLogger::log('Shift presensi ditambahkan', $shift);

        return redirect()
            ->route('admin.presensi-setting.index', ['shift' => $shift->id])
            ->with('success', 'Shift "'.$shift->name.'" berhasil ditambahkan.');
    }

    public function updateShift(Request $request, PresensiShift $presensiShift)
    {
        $this->authorizeKelolaShift();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());
        $shift = $this->findTenantShift($sekolah, $presensiShift->id);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('presensi_shifts', 'name')
                    ->where(fn ($q) => $q->where('sekolah_id', $sekolah->id))
                    ->ignore($shift->id),
            ],
        ]);

        $shift->update(['name' => $validated['name']]);

        return redirect()
            ->route('admin.presensi-setting.index', ['shift' => $shift->id])
            ->with('success', 'Nama shift diperbarui.');
    }

    public function destroyShift(PresensiShift $presensiShift)
    {
        $this->authorizeKelolaShift();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());
        $shift = $this->findTenantShift($sekolah, $presensiShift->id);

        if ($shift->is_default) {
            throw ValidationException::withMessages([
                'shift' => 'Shift default tidak dapat dihapus.',
            ]);
        }

        if ($sekolah->presensiShifts()->count() <= 1) {
            throw ValidationException::withMessages([
                'shift' => 'Minimal harus ada satu shift.',
            ]);
        }

        if ($shift->pengajars()->exists()) {
            throw ValidationException::withMessages([
                'shift' => 'Shift masih dipakai guru. Pindahkan guru ke shift lain terlebih dahulu.',
            ]);
        }

        $shift->delete();

        ActivityLogger::log('Shift presensi dihapus', $sekolah, ['shift' => $shift->name]);

        return redirect()
            ->route('admin.presensi-setting.index')
            ->with('success', 'Shift "'.$shift->name.'" berhasil dihapus.');
    }

    public function storeLibur(Request $request)
    {
        $this->authorizePengaturanPresensi();

        $sekolah = Sekolah::findOrFail(TenantContext::requireSekolahId());

        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $libur = SekolahHariLibur::withoutSekolahScope()->firstOrCreate(
            [
                'sekolah_id' => $sekolah->id,
                'tanggal' => $validated['tanggal'],
            ],
            [
                'keterangan' => $validated['keterangan'] ?? null,
            ],
        );

        if (! $libur->wasRecentlyCreated) {
            $libur->update(['keterangan' => $validated['keterangan'] ?? null]);
        }

        ActivityLogger::log('Hari libur presensi ditambahkan', $sekolah, [
            'tanggal' => $validated['tanggal'],
        ]);

        return redirect()
            ->route('admin.presensi-setting.index', $request->only('shift'))
            ->with('success', 'Hari libur berhasil disimpan.');
    }

    public function destroyLibur(Request $request, SekolahHariLibur $hariLibur)
    {
        $this->authorizePengaturanPresensi();

        $sekolahId = TenantContext::requireSekolahId();

        abort_unless((int) $hariLibur->sekolah_id === (int) $sekolahId, 403);

        $tanggal = $hariLibur->tanggal?->toDateString();
        $hariLibur->delete();

        ActivityLogger::log('Hari libur presensi dihapus', null, [
            'tanggal' => $tanggal,
        ]);

        return redirect()
            ->route('admin.presensi-setting.index', $request->only('shift'))
            ->with('success', 'Hari libur berhasil dihapus.');
    }

    private function authorizePengaturanPresensi(): void
    {
        $user = auth()->user();
        if ($user?->hasAnyRole(['Admin Sekolah', 'Lembaga'])) {
            return;
        }

        abort_if($user?->hasAnyRole(['Pengajar', 'Wali Kelas']), 403);
        abort_unless($user?->can('menu.pengaturan-presensi-guru'), 403);
    }

    private function authorizeKelolaShift(): void
    {
        abort_unless(auth()->user()?->hasRole('Admin Sekolah'), 403);
    }

    private function findTenantShift(Sekolah $sekolah, int $shiftId): PresensiShift
    {
        return PresensiShift::withoutSekolahScope()
            ->where('sekolah_id', $sekolah->id)
            ->whereKey($shiftId)
            ->firstOrFail();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PresensiShift>  $shifts
     */
    private function resolveActiveShift($shifts, int $requestedId): PresensiShift
    {
        if ($requestedId > 0) {
            $match = $shifts->firstWhere('id', $requestedId);
            if ($match !== null) {
                return $match;
            }
        }

        return $shifts->firstWhere('is_default', true) ?? $shifts->firstOrFail();
    }

    private function pastikanShiftDanJadwalLengkap(Sekolah $sekolah): void
    {
        $shift = PresensiShift::withoutSekolahScope()
            ->where('sekolah_id', $sekolah->id)
            ->where('is_default', true)
            ->first();

        if ($shift === null) {
            $shift = PresensiShift::withoutSekolahScope()->create([
                'sekolah_id' => $sekolah->id,
                'name' => 'Reguler',
                'location_coordinate' => $sekolah->location_coordinate,
                'radius_m' => $sekolah->radius_m,
                'is_default' => true,
                'sort_order' => 0,
            ]);
        }

        $this->pastikanJadwalLengkapUntukShift($sekolah, $shift);

        PresensiShift::withoutSekolahScope()
            ->where('sekolah_id', $sekolah->id)
            ->where('id', '!=', $shift->id)
            ->get()
            ->each(function (PresensiShift $other) use ($sekolah): void {
                $this->pastikanJadwalLengkapUntukShift($sekolah, $other);
            });
    }

    private function pastikanJadwalLengkapUntukShift(Sekolah $sekolah, PresensiShift $shift): void
    {
        $ada = SekolahJamKerja::withoutSekolahScope()
            ->where('presensi_shift_id', $shift->id)
            ->pluck('hari')
            ->all();

        foreach (range(1, 7) as $hari) {
            if (in_array($hari, $ada, true)) {
                continue;
            }

            $kerja = $hari <= 5;

            SekolahJamKerja::withoutSekolahScope()->create([
                'sekolah_id' => $sekolah->id,
                'presensi_shift_id' => $shift->id,
                'hari' => $hari,
                'is_kerja' => $kerja,
                'jam_masuk' => $kerja ? '07:00' : null,
                'jam_pulang' => $kerja ? '13:00' : null,
                'toleransi_masuk_menit' => $kerja ? 10 : 0,
                'toleransi_pulang_menit' => $kerja ? 10 : 0,
            ]);
        }
    }
}
