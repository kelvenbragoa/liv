<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $guarded = [];

    public function method(){
        return $this->hasOne('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }
}
