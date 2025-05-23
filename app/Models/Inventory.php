<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'user_id' => 'integer',
    'stock_center_id' => 'integer',
    'products_number' => 'integer',
];
     public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\InventoryItem','inventory_id','id');
    }
}

