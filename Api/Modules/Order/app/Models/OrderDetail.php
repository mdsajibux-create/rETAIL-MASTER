<?php

namespace Modules\Order\app\Models;

use App\Traits\RoundNumericFields;
use Illuminate\Database\Eloquent\Model;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductVariant;

class OrderDetail extends Model
{
    use RoundNumericFields;

    protected $fillable = [
        'order_id',
        'zone_id',
        'product_id',
        'behaviour',
        'product_sku',
        'variant_details',
        'base_price',
        'product_campaign_id',
        'discount_type',
        'discount_rate',
        'discount_amount',
        'price',
        'quantity',
        'line_total_price_with_qty',
        'line_total_excluding_tax',
        'tax_rate',
        'tax_amount',
        'total_tax_amount',
        'line_total_price',
        'coupon_discount_amount',
        'variant_id',
    ];
    protected array $excludedFieldsFromRounding = ['coupon_discount_amount', 'line_total_excluding_tax', 'line_total_price'];


    protected $casts = [
        'quantity' => 'integer'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productVariant()
    {
        return $this->hasOne(ProductVariant::class, 'sku', 'product_sku');
    }


}
