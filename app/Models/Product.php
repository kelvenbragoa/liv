<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    //
    protected $guarded = [];

    protected $casts = [
    'department_id' => 'integer',
    'category_id' => 'integer',
    'sub_category_id' => 'integer',
    'price' => 'float',
    'stock' => 'integer',
];

    public function category(){
        return $this->hasOne('App\Models\Category', 'id', 'category_id');
    }

    public function subcategory(){
        return $this->hasOne('App\Models\SubCategory', 'id', 'sub_category_id');
    }

    public function orderitens()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stockCenterProducts()
    {
        return $this->hasMany(StockCenterProduct::class);
    }

    public function getQuantityInPrincipalStockAttribute($value)
    {
        return (int) $value;  // Converte o valor para inteiro
    }

    // public function quantityInPrincipalStock()
    // {
    //     // Encontra o stock_center principal
    //     $principalStockCenter = StockCenter::where('is_principal_stock', 1)->first();

    //     if ($principalStockCenter) {
    //         // Retorna a quantidade do produto no estoque principal
    //         $stockCenterProduct = $this->stockCenterProducts()
    //             ->where('stock_center_id', $principalStockCenter->id)
    //             ->first();

    //         return $stockCenterProduct ? $stockCenterProduct->quantity : 0;
    //     }

    //     return 0;
    // }
    public function quantityInPrincipalStock()
{
    static $principalStockCenterId = null;

    if (is_null($principalStockCenterId)) {
        $principalStockCenterId = StockCenter::where('is_principal_stock', 1)->value('id');
    }

    if ($principalStockCenterId) {
        return (int) $this->stockCenterProducts()
            ->where('stock_center_id', $principalStockCenterId)
            ->value('quantity') ?? 0;
    }

    return 0;
}
    public function scopeWithQuantityInPrincipalStock($query)
{
    return $query->addSelect([
        'quantity_in_principal_stock' => StockCenterProduct::selectRaw('CAST(quantity AS SIGNED)')
            ->whereColumn('product_id', 'products.id')
            ->whereHas('stockCenter', function ($q) {
                $q->where('is_principal_stock', 1);
            })
            ->limit(1)
    ]);
}
}
