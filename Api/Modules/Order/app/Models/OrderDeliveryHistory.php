<?php

namespace Modules\Order\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrderDeliveryHistory extends Model
{
    protected $fillable = [
        'order_id',
        'deliveryman_id',
        'status',
        'reason'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function deliveryman()
    {
        return $this->belongsTo(User::class, 'deliveryman_id');
    }
}
