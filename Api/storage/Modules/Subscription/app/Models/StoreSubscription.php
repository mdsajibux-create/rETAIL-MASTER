<?php

namespace Modules\Subscription\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;

class StoreSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'subscription_id',
        'name',
        'validity',
        'price',
        'pos_system',
        'self_delivery',
        'mobile_app',
        'live_chat',
        'order_limit',
        'product_limit',
        'product_featured_limit',
        'payment_gateway',
        'payment_status',
        'transaction_ref',
        'manual_image',
        'expire_date',
        'status',
    ];

    public function scopeNotExpired($query)
    {
        return $query->where('expire_date', '>', now());
    }

    public function subscriptionHistories()
    {
        return $this->hasMany(SubscriptionHistory::class, 'store_subscription_id');
    }

    public function store()
    {
        return $this->belongsTo(Branch::class, 'store_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

}