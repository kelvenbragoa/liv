<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'stock_center_id' => 'integer',
    'inventory_id' => 'integer',
    'product_id' => 'integer',
    'quantity' => 'integer',
    'last_quantity' => 'integer',
];
     public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }
}

