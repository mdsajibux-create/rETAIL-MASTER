<?php

namespace Modules\Deliveryman\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use DeleteTranslations;

    protected $fillable = [
        'name',
        'capacity',
        'speed_range',
        'fuel_type',
        'max_distance',
        'extra_charge',
        'average_fuel_cost',
        'description',
        'status',
    ];

    public $translationKeys = [
        'name',
        'description',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
