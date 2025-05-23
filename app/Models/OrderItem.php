<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    //
    use Blameable;
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
    'order_id' => 'integer',
    'product_id' => 'integer',
    'department_id' => 'integer',
    'quantity' => 'integer',
    'order_item_status_id' => 'integer',
    'user_id' => 'integer',
    'price' => 'float',
    'total' => 'float',
    'cash_register_id' => 'integer',
    'prepared_by_user_id' => 'integer',
    'ready_by_user_id' => 'integer',
    'delivered_by_user_id' => 'integer',
    'created_by'=> 'integer',
    'updated_by'=> 'integer',
];

    public function product(){
        return $this->hasOne('App\Models\Product', 'id', 'product_id');
    }

    public function table(){
        return $this->hasOne('App\Models\Table', 'id', 'table_id');
    }

    public function order(){
        return $this->hasOne('App\Models\Order', 'id', 'order_id');
    }

    public function status(){
        return $this->hasOne('App\Models\OrderItemStatus', 'id', 'order_item_status_id	');
    }

    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function preparedby(){
        return $this->hasOne('App\Models\User', 'id', 'prepared_by_user_id');
    }

    public function readyby(){
        return $this->hasOne('App\Models\User', 'id', 'ready_by_user_id');
    }

    public function deliveredby(){
        return $this->hasOne('App\Models\User', 'id', 'delivered_by_user_id');
    }

    public function updatedby(){
        return $this->hasOne('App\Models\User', 'id', 'updated_by');
    }



    
}
