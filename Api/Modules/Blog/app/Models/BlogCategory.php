<?php

namespace Modules\Blog\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;
use Modules\Product\app\Models\Product;

class BlogCategory extends Model
{
    use DeleteTranslations;
    protected $fillable = [
        "name",
        "slug",
        "meta_title",
        "meta_description",
        "status",
    ];
    public $translationKeys = [
        'name',
        'meta_title',
        'meta_description',
    ];

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function blogs()
    {
        return $this->belongsToMany(Product::class);
    }
    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
