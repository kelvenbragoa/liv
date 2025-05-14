<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
        'table_id' => 'integer',
        'customer_id' => 'integer',
        'resarvation_status_id' => 'integer',
        'reservation_time' => 'datetime',
    ];
}
