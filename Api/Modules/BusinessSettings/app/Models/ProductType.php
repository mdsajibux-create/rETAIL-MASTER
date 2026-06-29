<?php

namespace Modules\BusinessSettings\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use DeleteTranslations;

    protected $fillable = [
        'name',
        'image',
        'description',
        'charge_status',
        'charge_name',
        'charge_amount',
        'charge_type',
        'status'
    ];


    public $translationKeys = [
        'name',
        'description',
        'additional_charge_name',
    ];

    public $casts = [
        'charge_status' => 'boolean',
    ];

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }

}
