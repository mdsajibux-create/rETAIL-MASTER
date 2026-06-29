<?php

namespace Modules\Product\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class ProductView extends Model
{
    protected $fillable = ['product_id', 'user_id', 'ip_address'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(Customer::class);
    }
}
