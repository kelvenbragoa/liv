<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockSnapshot extends Model
{
    //
    protected $guarded = [];
    protected $casts = [
    'product_id' => 'integer',
    'quantity' => 'integer',
    'date' => 'date',
];
}
