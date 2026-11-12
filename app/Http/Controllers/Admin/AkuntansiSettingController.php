<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\AkuntansiSetting;
use App\Models\SumberDana;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AkuntansiSettingController extends Controller
{
    public function index()
    {
        $sekolahId = auth()->user()->sekolah_id;
        $setting = AkuntansiSetting::forSekolah($sekolahId)->load(['akunKas', 'akunKasGaji', 'akunBebanGaji']);

        $jenisAkunAset = $setting->jenisUntukAkunAset();
        $jenisOptions = Akun::where('sekolah_id', $sekolahId)
            ->whereNotNull('jenis')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis')
            ->all();
        foreach ($jenisAkunAset as $jenis) {
            if (! in_array($jenis, $jenisOptions, true)) {
                $jenisOptions[] = $jenis;
            }
        }
        sort($jenisOptions);

        $akunAset = Akun::where('sekolah_id', $sekolahId)->where('is_aktif', true)->whereIn('jenis', $jenisAkunAset)->orderBy('kode')->get();
        $akunAset = $this->ensureAkunInOptions($akunAset, $sekolahId, $setting->akun_kas_id, $setting->akun_kas_gaji_id);
        $akunPendapatan = Akun::where('sekolah_id', $sekolahId)->where('is_aktif', true)->where('jenis', \App\Support\JenisAkun::PENDAPATAN)->orderBy('kode')->get();
        $akunPendapatan = $this->ensureAkunInOptions($akunPendapatan, $sekolahId, $setting->akun_pendapatan_id, $setting->akun_untuk_in);
        $akunBeban = Akun::where('sekolah_id', $sekolahId)->where('is_aktif', true)->where('jenis', \App\Support\JenisAkun::BEBAN)->orderBy('kode')->get();
        $akunBeban = $this->ensureAkunInOptions($akunBeban, $sekolahId, $setting->akun_untuk_out, $setting->akun_beban_gaji_id);
        $akunPiutangOptions = Akun::where('sekolah_id', $sekolahId)->where('is_aktif', true)->where('jenis', \App\Support\JenisAkun::ASSETS)->orderBy('kode')->get();
        $akunPiutangOptions = $this->ensureAkunInOptions($akunPiutangOptions, $sekolahId, $setting->akun_piutang_id);
        $sumberDanas = SumberDana::where('sekolah_id', $sekolahId)->aktif()->orderBy('urutan')->get();

        return view('admin.akuntansi-setting.index', compact(
            'setting', 'akunAset', 'akunPendapatan', 'akunBeban', 'akunPiutangOptions', 'jenisOptions', 'jenisAkunAset', 'sumberDanas'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'metode_pencatatan' => 'required|in:cash,accrual',
            'akun_kas_id' => 'required|exists:akuns,id',
            'akun_piutang_id' => 'required|exists:akuns,id',
            'akun_pendapatan_id' => 'required|exists:akuns,id',
            'akun_untuk_in' => 'required|exists:akuns,id',
            'akun_untuk_out' => 'required|exists:akuns,id',
            'akun_beban_gaji_id' => 'required|exists:akuns,id',
            'akun_kas_gaji_id' => 'required|exists:akuns,id',
            'sumber_dana_gaji_id' => 'nullable|exists:sumber_danas,id',
            'jenis_akun_aset' => 'required|array|min:1',
            'jenis_akun_aset.*' => 'string|max:50',
        ]);

        $sekolahId = auth()->user()->sekolah_id;
        $this->assertAkunMilikSekolah($sekolahId, [
            $request->input('akun_kas_id'),
            $request->input('akun_piutang_id'),
            $request->input('akun_pendapatan_id'),
            $request->input('akun_untuk_in'),
            $request->input('akun_untuk_out'),
            $request->input('akun_beban_gaji_id'),
            $request->input('akun_kas_gaji_id'),
        ]);

        if ($request->filled('sumber_dana_gaji_id')
            && ! SumberDana::where('sekolah_id', $sekolahId)->whereKey($request->input('sumber_dana_gaji_id'))->exists()) {
            throw ValidationException::withMessages([
                'sumber_dana_gaji_id' => 'Sumber dana tidak valid untuk sekolah ini.',
            ]);
        }

        $setting = AkuntansiSetting::forSekolah($sekolahId);

        $setting->update($request->only([
            'metode_pencatatan', 'akun_kas_id', 'akun_piutang_id',
            'akun_pendapatan_id', 'akun_untuk_in', 'akun_untuk_out',
            'akun_beban_gaji_id', 'sumber_dana_gaji_id', 'akun_kas_gaji_id',
        ]) + [
            'jenis_akun_aset' => array_values(array_unique(array_map('trim', $request->input('jenis_akun_aset', [])))),
            'sumber_dana_gaji_id' => $request->filled('sumber_dana_gaji_id') ? $request->input('sumber_dana_gaji_id') : null,
        ]);

        return redirect()->route('admin.akuntansi-setting.index')->with('success', 'Pengaturan akuntansi berhasil disimpan.');
    }

    /**
     * @param  Collection<int, Akun>  $options
     * @return Collection<int, Akun>
     */
    private function ensureAkunInOptions(Collection $options, int $sekolahId, int|string|null ...$ids): Collection
    {
        foreach (array_filter(array_unique($ids)) as $id) {
            if ($options->contains('id', (int) $id)) {
                continue;
            }
            $extra = Akun::where('sekolah_id', $sekolahId)->whereKey($id)->first();
            if ($extra) {
                $options = $options->push($extra);
            }
        }

        return $options->sortBy('kode')->values();
    }

    /** @param  array<int, int|string|null>  $akunIds */
    private function assertAkunMilikSekolah(int $sekolahId, array $akunIds): void
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $akunIds))));
        if ($ids === []) {
            return;
        }

        $valid = Akun::where('sekolah_id', $sekolahId)->whereIn('id', $ids)->count();
        if ($valid !== count($ids)) {
            throw ValidationException::withMessages([
                'akun_kas_id' => 'Satu atau lebih akun tidak valid untuk sekolah ini.',
            ]);
        }
    }
}
