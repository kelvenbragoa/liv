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
        return $this->hasOne('App\Models\User', 'id', 'updeted_by');
    }



    
}
