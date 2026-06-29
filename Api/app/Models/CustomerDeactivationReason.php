<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDeactivationReason extends Model
{
    protected $fillable = [
        'customer_id',
        'reason',
        'description'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
