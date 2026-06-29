<?php

namespace Modules\Catalog\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use DeleteTranslations;
    protected $fillable = [
        "name",
        "order",
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
