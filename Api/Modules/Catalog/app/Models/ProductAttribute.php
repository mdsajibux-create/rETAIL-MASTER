<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use DeleteTranslations;


    protected $table = 'product_attributes';

    protected $guarded = [];
    public $translationKeys = [
        'name'
    ];

    public function attribute_values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id');
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
