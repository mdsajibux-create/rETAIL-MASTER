<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Order\app\Models\Order;

class LiveLocation extends Model
{
    protected $fillable = [
        'trackable_type',
        'trackable_id',
        'ref',
        'order_id',
        'latitude',
        'longitude',
        'last_updated',
    ];
    protected $casts = [
        'last_updated' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the parent trackable model (deliveryman).
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the related order, if any.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
