<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Product\app\Models\Product;

class Wishlist extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
