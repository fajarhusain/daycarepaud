<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiIdempotencyKey extends Model
{
    protected $fillable = [
        'user_id',
        'key',
        'method',
        'path',
        'request_hash',
        'status',
        'response_status',
        'response_body',
        'response_headers',
        'locked_until',
        'expires_at',
    ];

    protected $hidden = [
        'response_body',
        'response_headers',
    ];

    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_headers' => 'array',
            'locked_until' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
