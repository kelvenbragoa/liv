<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCenterProduct extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'product_id' => 'integer',
    'stock_center_id' => 'integer',
    'quantity' => 'integer',
    ];


     public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }


    public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }
}

