<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;

class CustomerAddress extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'customer_id',
        'title',
        'type',
        'email',
        'contact_number',
        'address',
        'latitude',
        'longitude',
        'zone_id',
        'state_id',
        'city_id',
        'area_id',
        'road',
        'house',
        'floor',
        'postal_code',
        'is_default',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

}
