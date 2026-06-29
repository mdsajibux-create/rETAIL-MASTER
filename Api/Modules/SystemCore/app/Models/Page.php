<?php

namespace Modules\SystemCore\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use DeleteTranslations;

    protected $fillable = [
        'theme_name',
        'page_type',
        'layout',
        'page_class',
        'enable_builder',
        'show_breadcrumb',
        'page_parent',
        'page_order',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
    ];

    public $translationKeys = [
        'title',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];


    public function getContentAttribute($value)
    {

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : $value;
        }

        return $value;
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
