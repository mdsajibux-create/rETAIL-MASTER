<?php

namespace Modules\Location\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'sort_order',
        'delivery_charge'
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public $translationKeys = [
        'name',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function activeCities(): HasMany
    {
        return $this->hasMany(City::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
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
