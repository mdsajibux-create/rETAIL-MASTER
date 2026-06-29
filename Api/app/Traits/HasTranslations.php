<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasTranslations
{
    public static function bootHasTranslations()
    {
        static::deleting(function ($model) {
            // Check if model has related_translations method
            if (method_exists($model, 'related_translations')) {
                $model->related_translations()->delete();
            }
        });
    }

    public function related_translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translation::class, 'translatable_id')
            ->where('translatable_type', static::class);
    }

}
