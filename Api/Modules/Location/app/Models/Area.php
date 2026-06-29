<?php

namespace Modules\Location\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{

    use  SoftDeletes;

    protected $fillable = ['city_id', 'name', 'zip_code', 'is_active', 'sort_order', 'delivery_charge'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public $translationKeys = [
        'name',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function state()
    {
        return $this->city->state;
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

    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
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
