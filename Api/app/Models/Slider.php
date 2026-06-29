<?php

namespace App\Models;

use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use DeleteTranslations;
    protected $fillable = [
        'platform',
        'title',
        'sub_title',
        'description',
        'image',
        'bg_image',
        'bg_color',
        'button_text',
        'button_url',
        'redirect_url',
        'title_color',
        'sub_title_color',
        'description_color',
        'button_text_color',
        'button_bg_color',
        'button_hover_color',
        'order',
        'status',
        'created_by',
        'updated_by'
    ];
    public $translationKeys = [
        'title',
        'sub_title',
        'description',
        'button_text',
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
