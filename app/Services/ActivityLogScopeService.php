<?php

namespace App\Services;

use App\Models\User;
use App\Support\PaginationPerPage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogScopeService
{
    public function paginateForUser(User $user, Request $request): LengthAwarePaginator
    {
        $query = Activity::query()
            ->where('log_name', 'admin')
            ->with(['causer', 'subject'])
            ->latest('id');

        if ($user->hasRole('Superadmin')) {
            // ponytail: no filter  platform-wide
        } elseif ($user->hasRole('Lembaga')) {
            $query->where('properties->lembaga_id', $user->lembaga_id);
        } elseif ($user->sekolah_id && ($user->hasRole('Admin Sekolah') || $user->can('menu.log-aktivitas'))) {
            $query->where('properties->sekolah_id', $user->sekolah_id);
        } else {
            abort(403);
        }

        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }

        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', 'like', '%'.str_replace('\\', '\\\\', $subjectType).'%');
        }

        return $query->paginate(PaginationPerPage::resolve($request))->withQueryString();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Activity>
     */
    public function getForUser(User $user, Request $request)
    {
        $query = Activity::query()
            ->where('log_name', 'admin')
            ->with(['causer', 'subject'])
            ->latest('id');

        if ($user->hasRole('Superadmin')) {
            // ponytail: no filter
        } elseif ($user->hasRole('Lembaga')) {
            $query->where('properties->lembaga_id', $user->lembaga_id);
        } elseif ($user->sekolah_id && ($user->hasRole('Admin Sekolah') || $user->can('menu.log-aktivitas'))) {
            $query->where('properties->sekolah_id', $user->sekolah_id);
        } else {
            abort(403);
        }

        if ($event = $request->query('event')) {
            $query->where('event', $event);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($subjectType = $request->query('subject_type')) {
            $query->where('subject_type', 'like', '%'.str_replace('\\', '\\\\', $subjectType).'%');
        }

        return $query->get();
    }

    public function subjectLabel(Activity $activity): string
    {
        if (! $activity->subject_type) {
            return $this->humanizeDescription($activity->description);
        }

        $typeLabel = $this->typeLabel($activity->subject_type);

        if ($name = $this->resolveSubjectName($activity)) {
            return "{$typeLabel}: {$name}";
        }

        $id = $activity->subject_id;

        return $id ? "{$typeLabel} #{$id}" : $typeLabel;
    }

    private function typeLabel(?string $subjectType): string
    {
        $map = [
            'Anak' => 'Siswa',
            'User' => 'Pengguna',
            'Kelas' => 'Kelas',
            'Lembaga' => 'Lembaga',
            'Sekolah' => 'Sekolah',
            'Pengajar' => 'Pengajar',
            'CmsContent' => 'Konten CMS',
            'AiSetting' => 'Pengaturan AI',
            'AiProviderSlot' => 'Slot Provider AI',
            'Jurnal' => 'Jurnal',
            'JurnalLine' => 'Baris Jurnal',
            'Akun' => 'Akun',
            'Cashflow' => 'Cashflow',
            'Rkas' => 'RKAS',
            'RkasLine' => 'Baris RKAS',
            'RkasLineAnggaran' => 'Anggaran RKAS',
            'RkasRealisasi' => 'Realisasi RKAS',
            'SumberDana' => 'Sumber Dana',
            'BiayaBulananSekolah' => 'Biaya Bulanan',
            'BiayaBulananSiswa' => 'Biaya Siswa',
            'Diskon' => 'Diskon',
            'PembayaranBulanan' => 'Pembayaran Bulanan',
            'PembayaranBulananItem' => 'Item Pembayaran',
            'PembayaranBulananDetail' => 'Detail Pembayaran',
            'KritikSaran' => 'Kritik & Saran',
            'AkuntansiSetting' => 'Setting Akuntansi',
            'SekolahAiPersona' => 'Persona AI',
            'SekolahAiSetting' => 'Setting AI',
            'SekolahAiToken' => 'Token AI',
            'Presensi' => 'Presensi Siswa',
            'PresensiPengajar' => 'Presensi Guru',
            'Kesehatan' => 'Kesehatan Siswa',
            'Matrikulasi' => 'Matrikulasi',
            'SkalaPencapaian' => 'Skala Capaian',
            'Kegiatan' => 'Agenda Belajar',
            'MasterKegiatanRutin' => 'Kegiatan Rutin',
            'KegiatanRutin' => 'Jadwal Rutin',
            'Pencapaian' => 'Pencapaian',
            'Sarana' => 'Sarana',
            'MenuMakanan' => 'Menu Makanan',
        ];

        $short = class_basename((string) $subjectType);

        return $map[$short] ?? $short;
    }

    private function resolveSubjectName(Activity $activity): ?string
    {
        if ($activity->relationLoaded('subject') && $activity->subject) {
            $name = $this->displayNameFor($activity->subject);
            if ($name) {
                return $name;
            }
        }

        $attrs = $activity->properties['attributes'] ?? [];
        $old = $activity->properties['old'] ?? [];

        foreach (['name', 'title', 'nama', 'key', 'no_jurnal', 'deskripsi', 'email'] as $field) {
            $value = $attrs[$field] ?? $old[$field] ?? null;
            if (filled($value)) {
                return (string) $value;
            }
        }

        if (filled($attrs['kode'] ?? $old['kode'] ?? null)) {
            $kode = $attrs['kode'] ?? $old['kode'];
            $uraian = $attrs['uraian'] ?? $old['uraian'] ?? $attrs['nama'] ?? $old['nama'] ?? null;

            return $uraian ? "{$kode}  {$uraian}" : (string) $kode;
        }

        return null;
    }

    private function displayNameFor(object $subject): ?string
    {
        if ($subject instanceof User) {
            return $subject->email
                ? "{$subject->name} ({$subject->email})"
                : $subject->name;
        }

        if (isset($subject->label) && filled($subject->label)) {
            return (string) $subject->label;
        }

        foreach (['name', 'title', 'nama', 'key', 'no_jurnal'] as $field) {
            if (filled($subject->{$field} ?? null)) {
                return (string) $subject->{$field};
            }
        }

        if (filled($subject->deskripsi ?? null)) {
            $text = (string) $subject->deskripsi;

            return mb_strlen($text) > 50 ? mb_substr($text, 0, 50).'…' : $text;
        }

        if (filled($subject->kode ?? null)) {
            $uraian = $subject->uraian ?? $subject->nama ?? null;

            return $uraian ? "{$subject->kode}  {$uraian}" : (string) $subject->kode;
        }

        return null;
    }

    private function humanizeDescription(?string $description): string
    {
        if (! $description) {
            return '-';
        }

        $map = [
            'Role ditetapkan ke pengguna' => 'Penetapan role pengguna',
            'Role pengguna diperbarui' => 'Perubahan role pengguna',
            'Role dibuat' => 'Role baru',
            'Role diperbarui' => 'Perubahan role',
            'Role dihapus' => 'Penghapusan role',
            'Permission role diperbarui' => 'Perubahan akses menu role',
            'Role Admin Sekolah ditetapkan' => 'Penetapan Admin Sekolah',
            'Role Superadmin ditetapkan' => 'Penetapan Superadmin',
            'Role Lembaga ditetapkan' => 'Penetapan Admin Lembaga',
            'Cabang aktif diubah' => 'Ganti cabang aktif',
            'Token AI ditambahkan' => 'Top-up token AI',
        ];

        return $map[$description] ?? $description;
    }

    public function changesSummary(Activity $activity): string
    {
        $attributes = $activity->properties['attributes'] ?? null;
        $old = $activity->properties['old'] ?? null;

        if (is_array($attributes) && is_array($old)) {
            $parts = [];
            foreach ($attributes as $key => $value) {
                $prev = $old[$key] ?? null;
                if ($prev != $value) {
                    $parts[] = "{$key}: ".$this->stringify($prev).' → '.$this->stringify($value);
                }
            }

            return $parts ? implode('; ', array_slice($parts, 0, 5)) : '-';
        }

        if (is_array($attributes) && $activity->event === 'created') {
            return 'Data baru: '.implode(', ', array_slice(array_keys($attributes), 0, 5));
        }

        $extra = collect($activity->properties ?? [])
            ->except(['attributes', 'old', 'lembaga_id', 'sekolah_id', 'route_name', 'ip', 'user_agent'])
            ->all();

        if ($extra !== []) {
            return collect($extra)->map(fn ($v, $k) => "{$k}: ".$this->stringify($v))->implode('; ');
        }

        return $activity->description ?: '-';
    }

    private function stringify(mixed $value): string
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if ($value === null || $value === '') {
            return '(kosong)';
        }

        $text = (string) $value;

        return mb_strlen($text) > 40 ? mb_substr($text, 0, 40).'…' : $text;
    }
}
