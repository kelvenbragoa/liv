<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
    {
     protected $guarded = [];

     protected $casts = [
        'supplier_id' => 'integer',
        'purchase_order_status_id' => 'integer',
        'order_date' => 'date',
        'delivery_date' => 'date',
        'total_value' => 'float',
    ];
}

