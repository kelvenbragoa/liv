<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public const DIRECTION_IN = 'in';
    public const DIRECTION_OUT = 'out';

    public const REASON_SALE = 'sale';
    public const REASON_SALE_CANCEL = 'sale_cancel';
    public const REASON_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'stock_center_id',
        'product_id',
        'direction',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'stock_center_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'reference_id' => 'integer',
        'user_id' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function stockCenter()
    {
        return $this->belongsTo(StockCenter::class, 'stock_center_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
