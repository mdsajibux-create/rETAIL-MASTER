<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use DeleteTranslations;
    protected $fillable = [
        "name",
        "order",
        "created_by"
    ];
    public $translationKeys = [
        'name'
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
