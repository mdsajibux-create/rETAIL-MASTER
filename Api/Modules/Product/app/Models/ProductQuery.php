<?php

namespace Modules\Product\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductQuery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "product_id",
        "customer_id",
        "question",
        "reply",
        "replied_at",
        "status",
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, "customer_id");
    }

    public function product()
    {
        return $this->belongsTo(Product::class, "product_id");
    }
}
