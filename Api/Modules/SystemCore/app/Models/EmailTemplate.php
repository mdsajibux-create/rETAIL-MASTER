<?php

namespace Modules\SystemCore\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use DeleteTranslations;
    protected $fillable = ['type', 'name', 'subject', 'body', 'status'];

    protected $casts = [
        'status' => 'string',
        'body' => 'string',
    ];
    public $translationKeys = [
        'name',
        'subject',
        'body',
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
