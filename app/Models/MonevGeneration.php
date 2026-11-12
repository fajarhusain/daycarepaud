<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonevGeneration extends Model
{
    use BelongsToSekolah, HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'sekolah_id',
        'kelas_id',
        'tahun',
        'bulan',
        'sumber',
        'total',
        'completed',
        'skipped',
        'failed',
        'status',
        'errors',
        'triggered_by_user_id',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'errors' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'total' => 'integer',
        'completed' => 'integer',
        'skipped' => 'integer',
        'failed' => 'integer',
    ];

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    public function processed(): int
    {
        return $this->completed + $this->skipped + $this->failed;
    }

    public function progressPercent(): int
    {
        if ($this->total <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->processed() / $this->total) * 100));
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }

    public function appendError(string $message): void
    {
        $errors = $this->errors ?? [];
        $errors[] = $message;
        $this->errors = $errors;
    }
}
