<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

class DynamicField extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'product_type',
        'type',
        'options',
        'is_required',
        'status'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    public $translationKeys = [
        'name'
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }


    public function values()
    {
        return $this->hasMany(DynamicFieldValue::class, 'dynamic_field_id');
    }

    public function productFieldValues()
    {
        return $this->hasMany(DynamicFieldValue::class);
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
