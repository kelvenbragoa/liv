<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitNotes extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'user_id' => 'integer',
    'stock_center_id' => 'integer',
    'products_number' => 'integer',
    'supplier_id' => 'integer',
];


     public function stockcenter(){
        return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
    }

    public function supplier(){
        return $this->hasOne('App\Models\Supplier', 'id', 'supplier_id');
    }

    public function user(){
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }

    public function itens(){
        return $this->hasMany('App\Models\ExitNoteItem','exit_note_id','id');
    }
}

