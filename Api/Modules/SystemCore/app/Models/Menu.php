<?php

namespace Modules\SystemCore\app\Models;

use App\Models\Translation;
use App\Traits\DeleteTranslations;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use DeleteTranslations;

    protected $fillable = [
        'page_id',
        'name',
        'url',
        'icon',
        'position',
        'is_visible',
        'parent_id',
        'menu_level',
        'menu_path',
        'parent_path',
        'menu_content',
    ];
    protected $casts = [
        'menu_content' => 'json'
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

    // Self-referencing relationships for nesting
    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function page()
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}
