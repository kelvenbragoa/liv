<?php

namespace App\Models;

use App\Traits\Blameable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    //
    use Blameable;
    use SoftDeletes;
    protected $guarded = [];

    public function table(){
        return $this->hasOne('App\Models\Table', 'id', 'table_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\OrderItem','order_id','id');
    }

    public function itenswaiter(){
        return $this->hasMany('App\Models\OrderItem','order_id','id')->where('user_id',Auth::user()->id);
    }

    public function status(){
        return $this->hasOne('App\Models\OrderStatus', 'id', 'order_status_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function payment(){
        return $this->hasOne('App\Models\Payment', 'id', 'order_id');
    }

    public function total_consumed()
{
    return $this->itens()->sum('total');
}
}
