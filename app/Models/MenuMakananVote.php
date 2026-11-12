<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuMakananVote extends Model
{
    protected $fillable = [
        'user_id',
        'menu_makanan_id',
        'vote_type',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuMakanan(): BelongsTo
    {
        return $this->belongsTo(MenuMakanan::class);
    }
}
