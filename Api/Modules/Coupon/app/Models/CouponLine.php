<?php

namespace Modules\Coupon\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CouponLine extends Model
{
    use HasFactory;

    protected $fillable = [
        'coupon_id',
        'customer_id',
        'coupon_code',
        'discount_type',
        'discount',
        'min_order_value',
        'max_discount',
        'usage_limit',
        'usage_count',
        'start_date',
        'end_date',
        'status',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

}
