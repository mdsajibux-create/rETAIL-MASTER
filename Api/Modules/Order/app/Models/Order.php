<?php

namespace Modules\Order\app\Models;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\User;
use App\Traits\RoundNumericFields;
use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Feedback\app\Models\Review;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;

class Order extends Model
{
    use RoundNumericFields;
    protected $fillable = [
        'branch_id',
        'customer_id',
        'zone_id',
        'state_id',
        'city_id',
        'area_id',
        'shipping_address_id',
        'invoice_number',
        'invoice_date',
        'order_type',
        'delivery_option',
        'delivery_type',
        'delivery_time',
        'order_amount',
        'coupon_code',
        'coupon_title',
        'coupon_discount_amount',
        'product_discount_amount',
        'flash_discount_amount',
        'shipping_charge',
        'delivery_charge',
        'tax_amount',
        'additional_charge_name',
        'additional_charge_amount',
        'payment_gateway',
        'payment_status',
        'transaction_ref',
        'transaction_details',
        'order_notes',
        'default_currency_code',
        'currency_code',
        'exchange_rate',
        'is_reviewed',
        'confirmed_by',
        'confirmed_at',
        'cancel_request_by',
        'cancel_request_at',
        'cancelled_by',
        'cancelled_at',
        'delivery_completed_at',
        'refund_status',
        'payment_status',
        'status',
        'val_id',
    ];


    // This function generates invoice when the order is created
    protected static function booted()
    {
        static::creating(function ($order) {
            // 6 chars from date: YYMMDD
            $datePart = now()->format('ymd');
            // 9 chars from unique ID (alphanumeric, base36)
            $uniquePart = strtoupper(substr(base_convert(uniqid(), 16, 36), -9));
            $order->invoice_number = $datePart . $uniquePart;
            $order->invoice_date = now();
        });
    }

    public function shippingAddress()
    {
        return $this->belongsTo(CustomerAddress::class, 'order_id', 'id');
    }

    public function orderAddress()
    {
        return $this->hasOne(OrderAddress::class, 'order_id', 'id');
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function orderDeliveryHistory()
    {
        return $this->hasMany(OrderDeliveryHistory::class, 'order_id', 'id');
    }

    public function deliveryman()
    {
        return $this->belongsTo(User::class, 'confirmed_by', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function refund()
    {
        return $this->hasOne(OrderRefund::class, 'order_id', 'id');
    }

    public function orderActivities()
    {
        return $this->hasMany(OrderActivity::class, 'order_id', 'id');
    }

    public function isReviewedByCustomer(int $customerId, int $orderId, int $reviewableId, string $reviewableType): bool
    {
        return Review::where('customer_id', $customerId)
            ->where('order_id', $orderId)
            ->where('reviewable_type', $reviewableType)
            ->where('reviewable_id', $reviewableId)
            ->exists();
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }


}
