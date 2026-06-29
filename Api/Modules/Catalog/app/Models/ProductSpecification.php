<?php

namespace Modules\Catalog\app\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\app\Models\Product;

class ProductSpecification extends Model
{
    protected $fillable = [
        'product_id',
        'dynamic_field_id',
        'dynamic_field_value_id',
        'name',
        'type',
        'custom_value',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function dynamicField()
    {
        return $this->belongsTo(DynamicField::class, 'dynamic_field_id', 'id');
    }
    public function dynamicFieldValue()
    {
        return $this->belongsTo(DynamicFieldValue::class, 'dynamic_field_value_id', 'id');
    }

}

