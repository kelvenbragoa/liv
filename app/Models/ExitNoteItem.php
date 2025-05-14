<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExitNoteItem extends Model
    {
     protected $guarded = [];

     protected $casts = [
    'stock_center_id' => 'integer',
    'exit_note_id' => 'integer',
    'product_id' => 'integer',
    'quantity' => 'integer',
    'last_quantity' => 'integer',
];
     public function stockcenter(){
            return $this->hasOne('App\Models\StockCenter', 'id', 'stock_center_id');
        }

        public function product(){
            return $this->hasOne('App\Models\Product', 'id', 'product_id');
        }
}

