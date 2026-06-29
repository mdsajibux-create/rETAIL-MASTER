<?php

namespace Modules\Order\app\Models;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;

class OrderRefund extends Model
{
    protected $fillable = [
        "order_id",
        "customer_id",
        "order_refund_reason_id",
        "customer_note",
        "file",
        "status",
        "amount",
        "reject_reason",
        'branch_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderRefundReason()
    {
        return $this->belongsTo(OrderRefundReason::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
