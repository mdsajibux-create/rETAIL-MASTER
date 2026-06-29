<?php

namespace Modules\BusinessSettings\app\Models;

use Illuminate\Database\Eloquent\Model;

class StoreAreaSettingStoreType extends Model
{
    protected $fillable = [
        'store_area_setting_id',
        'store_type_id',
        'status'
    ];

    public function storeAreaSetting()
    {
        return $this->belongsTo(ZoneSetting::class, 'store_area_setting_id');
    }

    public function storeType()
    {
        return $this->belongsTo(ProductType::class, 'store_type_id');
    }

}
