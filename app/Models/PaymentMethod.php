<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    //
    protected $guarded = [];
    public function status(){
        return $this->hasOne('App\Models\Status', 'id', 'status_id');
    }
}
