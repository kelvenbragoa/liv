<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
    'order_id' => 'integer',
    'payment_method_id' => 'integer',
    'amount' => 'float',
    'user_id' => 'integer',
    'cash_register_id' => 'integer',
];

    public function method(){
        return $this->hasOne('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }
    public function order(){
        return $this->hasOne('App\Models\Order', 'id', 'order_id');
    }

}
