<?php

namespace Modules\BusinessSettings\app\Models;

use App\Models\CustomerAddress;
use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\Branch\app\Models\Branch;

class Zone extends Model
{
    use DeleteTranslations;

    //Will Manage  zone
    protected $table = 'zones';
    protected $guarded = [];
    protected $fillable = [
        'state',
        'name',
        'code',
        'coordinates',
        'center_latitude',
        'center_longitude',
        'state',
        'city',
        'status',
        'is_default',
        'created_by',
        'updated_by',
    ];
    protected $casts = [
        'coordinates' => Polygon::class,
    ];

    public $translationKeys = [
        'name',
        'state',
        'city',
    ];


    public function zoneTypeSettings()
    {
        return $this->hasOne(ZoneSetting::class, 'zone_id', 'id');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function stores()
    {
        return $this->hasMany(Branch::class, 'zone_id');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'zone_id');
    }

    // Method to get translation by language and key
    public function getTranslation(string $key, string $language)
    {
        return $this->translations()->where('language', $language)->where('key', $key)->first()->value ?? null;
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
