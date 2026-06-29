<?php

namespace Modules\Feedback\app\Models;

use App\Models\Customer;
use App\Models\ReviewReaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Order\app\Models\Order;

class Review extends Model
{
    use SoftDeletes;
    protected $fillable = [
        "order_id",
        "reviewable_id",
        "reviewable_type",
        "customer_id",
        "review",
        "rating",
        "status",
        "like_count",
        "dislike_count",
    ];


    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function reviewReactions()
    {
        return $this->hasMany(ReviewReaction::class, 'review_id');
    }

}
