<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lembaga extends Model
{
    use LogsScopedActivity;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'pendiri',
        'organisasi',
        'no_akta',
        'no_pengesahan',
        'status',
        'slug',
        'contact_name',
        'contact_email',
        'contact_phone',
        'rejection_reason',
        'approved_at',
        'approved_by',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return ($this->status ?? self::STATUS_ACTIVE) === self::STATUS_ACTIVE;
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sekolahs()
    {
        return $this->hasMany(Sekolah::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
