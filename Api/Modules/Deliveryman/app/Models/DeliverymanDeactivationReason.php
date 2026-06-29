<?php

namespace Modules\Deliveryman\app\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverymanDeactivationReason extends Model
{
    protected $fillable = [
        "deliveryman_id",
        "reason",
        "description",
    ];

    public function deliveryman()
    {
        return $this->belongsTo(Deliveryman::class);
    }
}
