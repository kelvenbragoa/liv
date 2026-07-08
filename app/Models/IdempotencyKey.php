<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $fillable = [
        'key',
        'user_id',
        'action',
        'status_code',
        'response_body',
    ];

    protected $casts = [
        'response_body' => 'array',
        'status_code' => 'integer',
    ];
}
