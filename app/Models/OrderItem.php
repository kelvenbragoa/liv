<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $guarded = [];
    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    public function table(){
        return $this->hasOne('App\Models\Table', 'id', 'table_id');
    }

    public function order(){
        return $this->hasOne('App\Models\Order', 'id', 'order_id');
    }
}
