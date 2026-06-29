<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory,DeleteTranslations;

    protected $table = 'product_category';

    protected $guarded = [];
    public $translationKeys = [
        'category_name',
        'meta_title',
        'meta_description',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    // Method to get translation by language and key
    public function getTranslation(string $key, string $language)
    {
        return $this->translations()->where('language', $language)->where('key', $key)->first()->value ?? null;
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
