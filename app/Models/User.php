<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\AiTokenService;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, LogsScopedActivity, Notifiable;

    protected array $activityLogExcept = ['password', 'remember_token'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'lembaga_id',
        'sekolah_id',
        'kelas_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tour_completed' => 'array',
        ];
    }

    public function markTourCompleted(string $route): void
    {
        $completed = $this->tour_completed ?? [];
        $completed[$route] = now()->toIso8601String();
        $this->tour_completed = $completed;
        $this->save();
    }

    public function lembaga()
    {
        return $this->belongsTo(Lembaga::class);
    }

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function pengajar()
    {
        return $this->hasOne(Pengajar::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function anaks()
    {
        return $this->hasMany(Anak::class, 'user_id');
    }

    public function kritikSarans()
    {
        return $this->hasMany(KritikSaran::class);
    }

    public function orangTuaChat()
    {
        return $this->hasOne(OrangTuaChat::class);
    }

    public function hasAnyMenuPermission(): bool
    {
        return $this->getAllPermissions()->contains(
            fn ($permission) => str_starts_with($permission->name, 'menu.')
        );
    }

    /**
     * null = seluruh kelas sekolah (admin). List = penempatan guru + kelas yang diwali.
     *
     * @return list<int>|null
     */
    public function scopedKelasIds(): ?array
    {
        if ($this->hasRole(['Admin Sekolah', 'Lembaga', 'Superadmin'])) {
            return null;
        }

        $pengajar = $this->pengajar;
        if (! $pengajar) {
            return null;
        }

        return array_map('intval', $pengajar->accessibleKelasIds());
    }

    public function applyScopedKelas($query, string $column = 'kelas_id')
    {
        $ids = $this->scopedKelasIds();
        if ($ids === null) {
            return $query;
        }

        $query->whereIn($column, $ids !== [] ? $ids : [-1]);

        return $query;
    }

    public function approvedSekolahIds(): array
    {
        return $this->anaks()
            ->where('status', 'approved')
            ->distinct()
            ->pluck('sekolah_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function usesAdminKegiatanRutinRoutes(): bool
    {
        return $this->hasRole(['Admin Sekolah', 'Wali Kelas']);
    }

    public function kegiatanRutinRoutePrefix(): string
    {
        return $this->usesAdminKegiatanRutinRoutes() ? 'admin.' : 'pengajar.';
    }

    public function canManageKegiatanRutinMaster(): bool
    {
        return $this->usesAdminKegiatanRutinRoutes();
    }

    public function canAccessAdminPanel(): bool
    {
        if ($this->hasRole('Orang Tua')) {
            return false;
        }

        if ($this->hasRole('Lembaga')) {
            return TenantContext::sekolahId() !== null;
        }

        if (! ($this->getAttributes()['sekolah_id'] ?? null)) {
            return false;
        }

        if ($this->hasRole('Admin Sekolah')) {
            return true;
        }

        return $this->hasAnyMenuPermission();
    }

    public function firstAccessibleAdminRoute(?bool $chatOrangTuaEnabled = null): ?string
    {
        $sekolahId = TenantContext::sekolahId();
        if ($sekolahId && $chatOrangTuaEnabled === null) {
            $chatOrangTuaEnabled = app(AiTokenService::class)
                ->isChatOrangTuaEnabled($sekolahId);
        }

        foreach (config('admin-menu.menu_order', []) as $item) {
            if (($item['perm'] ?? '') === 'menu.chat-orangtua' && ! $chatOrangTuaEnabled) {
                continue;
            }
            if ($this->can($item['perm'])) {
                return $item['route'];
            }
        }

        return null;
    }
}
