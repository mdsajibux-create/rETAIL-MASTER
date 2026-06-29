<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStockTransferItem extends Model
{
    protected $fillable = [
        'product_stock_transfer_id',
        'product_id',
        'variant_id',
        'qty_requested',
        'qty_dispatched',
        'qty_received',
        'unit_cost',
        'total_cost',
        'note',
        'status',
    ];

    protected $casts = [
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function transfer()
    {
        return $this->belongsTo(ProductStockTransfer::class, 'product_stock_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    // ── Auto-compute total_cost before save
    protected static function booted(): void
    {
        static::saving(function ($item) {
            if ($item->unit_cost && $item->qty_requested) {
                $item->total_cost = $item->unit_cost * $item->qty_requested;
            }
        });
    }

}
