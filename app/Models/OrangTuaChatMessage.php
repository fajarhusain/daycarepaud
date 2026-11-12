<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrangTuaChatMessage extends Model
{
    protected $table = 'orangtua_chat_messages';

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'orangtua_chat_id',
        'role',
        'content',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(OrangTuaChat::class, 'orangtua_chat_id');
    }
}
