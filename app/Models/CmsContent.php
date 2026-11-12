<?php

namespace App\Models;

use App\Models\Concerns\LogsScopedActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsContent extends Model
{
    use HasFactory, LogsScopedActivity;

    protected $fillable = ['sekolah_id', 'key', 'value'];

    /**
     * Get a CMS value by key. Optionally scope to a sekolah_id.
     * Falls back to global (null sekolah_id) if not found for sekolah.
     */
    public static function get(string $key, string $default = '', ?int $sekolahId = null): string
    {
        if ($sekolahId) {
            $content = static::where('sekolah_id', $sekolahId)->where('key', $key)->first();
            if ($content) {
                return $content->value ?? $default;
            }
        }
        // Fall back to global (null sekolah_id)
        $content = static::whereNull('sekolah_id')->where('key', $key)->first();

        return $content ? ($content->value ?? $default) : $default;
    }

    /**
     * Upsert a CMS key-value pair.
     */
    public static function set(string $key, ?string $value, ?int $sekolahId = null): void
    {
        static::updateOrCreate(
            ['sekolah_id' => $sekolahId, 'key' => $key],
            ['value' => $value]
        );
    }
}
