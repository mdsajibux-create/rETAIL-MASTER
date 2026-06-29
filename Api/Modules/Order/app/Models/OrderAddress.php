<?php

namespace Modules\Order\app\Models;

use Illuminate\Database\Eloquent\Model;

class OrderAddress extends Model
{
    protected $fillable = [
        'order_id',
        'zone_id',
        'type',
        'name',
        'email',
        'contact_number',
        'address',
        'latitude',
        'longitude',
        'road',
        'house',
        'floor',
        'postal_code',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
