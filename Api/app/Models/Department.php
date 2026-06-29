<?php

namespace App\Models;

use App\Traits\DeleteTranslations;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory , HasTranslations,DeleteTranslations;

    protected $fillable = [
        'name',
        'status'
    ];

    public $translationKeys = [
        'name',
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
