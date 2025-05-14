<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCenterTransferItem extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'stock_center_transfer_id' => 'integer',
    'product_id' => 'integer',
    'quantity' => 'integer',
    ];
     public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}

