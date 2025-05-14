<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SplitPayment extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
    'payment_id' => 'integer',
    'amount' => 'float',
];
}
