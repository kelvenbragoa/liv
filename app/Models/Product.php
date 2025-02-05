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

    // public function stock(){
    //     return $this->hasOneThrough(
    //         StockCenterProduct::class,
    //         StockCenter::class,
    //         'id', // Chave primária de StockCenter
    //         'stock_center_id', // Chave estrangeira em StockCenterProduct
    //         'id', // Chave primária de Product
    //         'id' // Chave estrangeira em StockCenterProduct
    //     )->where('stock_centers.is_principal_stock', 1);
    // }
}
