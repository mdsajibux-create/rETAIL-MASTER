<?php

namespace Modules\Deliveryman\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Feedback\app\Models\Review;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;

class DeliveryMan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'vehicle_type_id',
        'zone_id',
        'identification_type',
        'identification_number',
        'identification_photo_front',
        'identification_photo_back',
        'address',
        'status',
        'is_verified',
        'verified_at',
        'created_by',
        'updated_by',
        'state_id',
        'city_id',
        'area_id',
    ];

    public function reviews()
    {
        return $this->morphMany(Review::class,'reviewable');
    }

    public function scopePendingDeliveryman($query)
    {
        return $query->whereHas('deliveryman', function ($q) {
            $q->where('status', 0)
                ->where('activity_scope', 'delivery_level');
        });
    }

    public function vehicle_type()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
