<?php

namespace Modules\PaymentGateways\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentGateway extends Model
{
    use HasFactory;

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
