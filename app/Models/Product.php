<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $guarded = [];

    public function category(){
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function subcategory(){
        return $this->hasOne('App\Models\SubCategory', 'id', 'sub_category_id');
    }
}
