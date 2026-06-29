<?php

namespace Modules\BusinessSettings\app\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneSetting extends Model
{
    protected $fillable = [
        'zone_id',
        'delivery_time_per_km',
        'min_order_delivery_fee',
        'delivery_charge_method',
        'out_of_area_delivery_charge',
        'fixed_charge_amount',
        'per_km_charge_amount'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function productTypes()
    {
        return $this->belongsToMany(ProductType::class, 'zone_setting_product_types', 'zone_setting_id', 'product_type_id');
    }

    public function rangeCharges()
    {
        return $this->hasMany(ZoneSettingRangeCharge::class, 'zone_setting_id');
    }
}
