<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSnapshot extends Model
{
    //
    protected $guarded = [];
    protected $casts = [
    'product_id' => 'integer',
    'cash_session_id' => 'integer',
    'quantity' => 'integer',
    'date' => 'date',
];
}
