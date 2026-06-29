<?php

namespace Modules\Order\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;

class OrderRefundReason extends Model
{
    protected $fillable = [
        'reason'
    ];
    public $translationKeys = [
        'reason',
    ];

    public function orderRefunds()
    {
        return $this->hasMany(OrderRefund::class);
    }

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }
}
