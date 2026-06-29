<?php

namespace Modules\BusinessSettings\app\Models;

use Illuminate\Database\Eloquent\Model;

class ZoneSettingRangeCharge extends Model
{
    protected $fillable = [
        'store_area_setting_id',
        'min_km',
        'max_km',
        'charge_amount',
        'status'
    ];

    public function storeAreaSetting()
    {
        return $this->belongsTo(ZoneSetting::class);
    }

}
