<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCenter extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'is_principal_stock' => 'integer',
];
     
     public function stockcenterproducts(){
        return $this->hasMany('App\Models\StockCenterProduct','stock_center_id','id');
    }
}

