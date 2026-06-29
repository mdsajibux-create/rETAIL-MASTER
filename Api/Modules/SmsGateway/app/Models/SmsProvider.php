<?php

namespace Modules\SmsGateway\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmsProvider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'sms_providers';

    protected $fillable = [
        'name',
        'slug',
        'logo',
        'expire_time',
        'credentials',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'expire_time' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

}
