<?php

namespace App\Console\Commands;

use App\Models\AkuntansiSetting;
use App\Models\Jurnal;
use App\Models\PembayaranBulanan;
use Illuminate\Console\Command;
use App\Services\AkuntansiService;
use Illuminate\Support\Facades\DB;

class PerbaikiPiutangSpp extends Command
{
    protected $signature = 'akuntansi:perbaiki-piutang-spp {--apply : Simpan perubahan (default: dry-run)}';

    protected $description = 'Pindahkan kredit jurnal pelunasan SPP dari pendapatan ke piutang bila tagihannya sudah diakui sebagai piutang; buat jurnal tagihan yang hilang untuk pelunasan yang mengkredit piutang';

    public function handle(): int
    {
        $apply = $this->option('apply');
        $jumlah = 0;

        $pelunasan = DB::table('jurnals')
            ->where('sourceable_type', PembayaranBulanan::class)
            ->where('deskripsi', 'like', 'Auto: Pembayaran%')
            ->whereExists(fn ($q) => $q->selectRaw(1)->from('jurnals as t')
                ->whereColumn('t.sourceable_id', 'jurnals.sourceable_id')
                ->where('t.sourceable_type', PembayaranBulanan::class)
                ->where('t.deskripsi', 'like', 'Auto: Tagihan%'))
            ->get();

        foreach ($pelunasan as $j) {
            $setting = AkuntansiSetting::forSekolah($j->sekolah_id);
            if (! $setting->akun_piutang_id || ! $setting->akun_pendapatan_id) {
                continue;
            }

            $lines = DB::table('jurnal_lines')
                ->where('jurnal_id', $j->id)
                ->where('akun_id', $setting->akun_pendapatan_id)
                ->where('kredit', '>', 0);

            if ($lines->count() === 0) {
                continue;
            }

            $jumlah++;
            $this->line("Jurnal #{$j->id} ({$j->deskripsi}): kredit pendapatan → piutang");
            if ($apply) {
                $lines->update(['akun_id' => $setting->akun_piutang_id]);
            }
        }

        // Pelunasan yang mengkredit piutang tanpa jurnal tagihan: buat Dr Piutang / Cr Pendapatan.
        $yatim = DB::table('jurnals as j')
            ->join('jurnal_lines as l', 'l.jurnal_id', '=', 'j.id')
            ->join('akuntansi_settings as s', 's.sekolah_id', '=', 'j.sekolah_id')
            ->where('j.sourceable_type', PembayaranBulanan::class)
            ->where('j.deskripsi', 'like', 'Auto: Pembayaran%')
            ->whereColumn('l.akun_id', 's.akun_piutang_id')
            ->where('l.kredit', '>', 0)
            ->whereNotExists(fn ($q) => $q->selectRaw(1)->from('jurnals as t')
                ->whereColumn('t.sourceable_id', 'j.sourceable_id')
                ->where('t.sourceable_type', PembayaranBulanan::class)
                ->where('t.deskripsi', 'like', 'Auto: Tagihan%'))
            ->select('j.id', 'j.sekolah_id', 'j.sourceable_id', 'j.tanggal', 'j.deskripsi', 'j.created_by', 'l.kredit', 's.akun_piutang_id', 's.akun_pendapatan_id')
            ->get();

        $service = app(AkuntansiService::class);
        foreach ($yatim as $j) {
            $jumlah++;
            $this->line("Jurnal #{$j->id} ({$j->deskripsi}): buat jurnal tagihan {$j->kredit}");
            if (! $apply) {
                continue;
            }

            DB::transaction(function () use ($j, $service) {
                $tagihan = Jurnal::create([
                    'sekolah_id' => $j->sekolah_id,
                    'no_jurnal' => $service->generateNoJurnal($j->sekolah_id),
                    'tanggal' => $j->tanggal,
                    'deskripsi' => str_replace('Auto: Pembayaran', 'Auto: Tagihan', $j->deskripsi),
                    'created_by' => $j->created_by,
                    'source' => 'auto-pembayaran',
                    'sourceable_type' => PembayaranBulanan::class,
                    'sourceable_id' => $j->sourceable_id,
                ]);
                $tagihan->lines()->createMany([
                    ['akun_id' => $j->akun_piutang_id, 'debit' => $j->kredit, 'kredit' => 0],
                    ['akun_id' => $j->akun_pendapatan_id, 'debit' => 0, 'kredit' => $j->kredit],
                ]);
                // jurnal_id dipakai halaman tagihan; isi hanya bila kosong
                PembayaranBulanan::withoutGlobalScopes()->whereKey($j->sourceable_id)->whereNull('jurnal_id')->update(['jurnal_id' => $tagihan->id]);
            });
        }

        $this->info($apply ? "{$jumlah} jurnal diperbaiki." : "{$jumlah} jurnal akan diperbaiki. Jalankan dengan --apply.");

        return self::SUCCESS;
    }
}
