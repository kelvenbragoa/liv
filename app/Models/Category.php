<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
    'department_id' => 'integer',
];

    public function department(){
        return $this->hasOne('App\Models\Department', 'id', 'department_id');
    }

    public function sub_categories(){
        return $this->hasMany('App\Models\SubCategory','category_id','id');
    }

    public function products(){
        return $this->hasMany('App\Models\Product','category_id','id');
    }
}
