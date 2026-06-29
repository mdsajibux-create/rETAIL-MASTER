<?php

namespace Modules\Location\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
    use  SoftDeletes;

    protected $fillable = ['state_id', 'name', 'is_active', 'sort_order', 'delivery_charge'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public $translationKeys = [
        'name',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function activeAreas(): HasMany
    {
        return $this->hasMany(Area::class)->where('is_active', true)->orderBy('sort_order');
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByState($query, int $stateId)
    {
        return $query->where('state_id', $stateId);
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
