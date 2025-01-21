<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $guarded = [];

    public function table(){
        return $this->hasOne('App\Models\Table', 'id', 'table_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\OrderItem','order_id','id');
    }

    public function status(){
        return $this->hasOne('App\Models\OrderStatus', 'id', 'order_status_id');
    }
}
