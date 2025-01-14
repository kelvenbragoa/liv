<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    //
    protected $guarded = [];

    public function status(){
        return $this->hasOne('App\Models\TableStatus', 'id', 'table_status_id');
    }
}
