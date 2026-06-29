<?php

namespace Modules\PaymentGateways\app\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    /**
     * The attributes that are mass assignable.
     */

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'auth_credentials',
        'is_test_mode',
        'status',
    ];

}
