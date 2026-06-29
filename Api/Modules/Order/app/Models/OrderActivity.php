<?php

namespace Modules\Order\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class OrderActivity extends Model
{
    protected $table = 'order_activities';

    protected $fillable = [
        'order_id',
        'collected_by',
        'activity_from',
        'activity_type',
        'ref_id',
        'reference',
        'activity_value'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function ref()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }
}
