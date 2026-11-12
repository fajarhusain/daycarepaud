<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSekolah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrangTuaChat extends Model
{
    use BelongsToSekolah;

    protected $table = 'orangtua_chats';

    protected $fillable = [
        'user_id',
        'sekolah_id',
        'cleared_at',
    ];

    protected function casts(): array
    {
        return [
            'cleared_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrangTuaChatMessage::class, 'orangtua_chat_id')->orderBy('created_at');
    }

    public function visibleMessagesForUser()
    {
        $query = $this->messages();

        if ($this->cleared_at) {
            $query->where('created_at', '>', $this->cleared_at);
        }

        return $query;
    }
}
