<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

class DynamicFieldValue extends Model
{

    protected $fillable = [
        'dynamic_field_id',
        'value',
    ];

    public $translationKeys = [
        'value'
    ];
   public function dynamicField()
   {
       return $this->belongsTo(DynamicField::class, 'dynamic_field_id');
   }
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
