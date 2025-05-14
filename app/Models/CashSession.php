<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    //
    protected $guarded = [];
    protected $casts = [
    'user_id' => 'integer',
    'opened_at' => 'datetime',
    'closed_at' => 'datetime',
    'closing_balance' => 'float',
    'cash_session_status_id' => 'integer',
];
}
