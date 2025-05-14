<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockCenterTransfer extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'stock_center_transfer_status_id' => 'integer',
    'stock_center_origin_id' => 'integer',
    'stock_center_destination_id' => 'integer',
    'user_id' => 'integer',
    'transfer_date' => 'date', // Para garantir que a data seja tratada corretamente
    ];

     public function stockcenterorigin(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_origin_id');
    }

    public function stockcenterdestination(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_destination_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User','id','user_id');
    }


    public function itens(){
        return $this->hasMany('App\Models\StockCenterTransferItem','stock_center_transfer_id','id');
    }
}

