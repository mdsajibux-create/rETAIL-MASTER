<?php

namespace Modules\PaymentGateways\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_default',
        'status',
    ];
    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
        'exchange_rate' => 'float',
    ];
    public array $translationKeys = [
        'name'
    ];

    public function translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }

}
