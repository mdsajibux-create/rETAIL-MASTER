<?php

namespace Modules\Product\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlashSale extends Model
{
    use HasFactory,DeleteTranslations;

    protected $fillable = [
        'title',
        'title_color',
        'description',
        'description_color',
        'background_color',
        'button_text',
        'button_text_color',
        'button_hover_color',
        'button_bg_color',
        'button_url',
        'timer_bg_color',
        'timer_text_color',
        'image',
        'cover_image',
        'discount_type',
        'discount_amount',
        'special_price',
        'purchase_limit',
        'start_time',
        'end_time',
        'status',
    ];
    public $translationKeys = [
        'title',
        'description',
        'button_text'
    ];
    protected $casts = [
        'discount_amount' => 'float',
        'status' => 'boolean',
    ];

    protected static function booted()
    {
        static::retrieved(function ($model) {
            // 1. Expiration check
            if ($model->end_time && now()->gt($model->end_time) && $model->status != 0) {
                $model->updateQuietly(['status' => 0]);
                return;
            }

            // 2. Purchase limit check
            if (!is_null($model->purchase_limit) && $model->purchase_limit <= 0 && $model->status != 0) {
                $model->updateQuietly(['status' => 0]);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(FlashSaleProduct::class);
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}
