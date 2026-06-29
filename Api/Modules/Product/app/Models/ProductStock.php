<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;

class ProductStock extends Model
{

    protected $fillable = [
        'branch_id',
        'product_id',
        'qty',
        'qty_incoming',
        'qty_damaged',
        'reorder_point',
        'reorder_qty',
        'variant_id',
        'qty_reserved',
        'selling_price',
        'cost_price',
        'sale_price',
        'is_active',
        'is_featured',
        'last_restocked_at',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'last_counted_at'    => 'datetime',
        'last_restocked_at'  => 'datetime',
        'cost_price'         => 'decimal:4',
        'selling_price'      => 'decimal:4',
        'sale_price'         => 'decimal:4',
    ];


    // ─── Relationships

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
