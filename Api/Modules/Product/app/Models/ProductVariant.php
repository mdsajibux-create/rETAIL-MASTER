<?php

namespace Modules\Product\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Catalog\app\Models\Unit;
use Modules\Product\app\Models\Product;


class ProductVariant extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $fillable = [
        'product_id',
        'variant_slug',
        'sku',
        'price',
        'pack_quantity',
        'weight_major',
        'weight_gross',
        'weight_net',
        "attributes",
        'special_price',
        'unit_id',
        'length',
        'width',
        'height',
        'image',
        'order_count',
        'status',
    ];

    protected $casts = [
        'pack_quantity' => 'decimal:2',
        'weight_major' => 'decimal:2',
        'weight_gross' => 'decimal:2',
        'weight_net' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id");
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, "unit_id");
    }

    public function stockStatus($threshold = 10)
    {
        if ($this->stock_quantity > 0 && $this->stock_quantity < $threshold) {
            return 'low_stock';
        } elseif ($this->stock_quantity === 0) {
            return 'out_of_stock';
        } else {
            return 'in_stock';
        }
    }

    public function stock()
    {
        return $this->hasOne(ProductStock::class, 'variant_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'variant_id');
    }
}
