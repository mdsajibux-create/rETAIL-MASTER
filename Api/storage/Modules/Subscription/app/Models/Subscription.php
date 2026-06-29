<?php

namespace Modules\Subscription\app\Models;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Subscription\Database\Factories\SubscriptionFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'validity',
        'price',
        'image',
        'description',
        'pos_system',
        'self_delivery',
        'mobile_app',
        'review_system',
        'live_chat',
        'order_limit',
        'product_limit',
        'product_featured_limit',
        'start_date',
        'end_date',
        'status',
    ];

    public $translationKeys = [
        'name',
        'description'
    ];

    protected $hidden = [
        // Add sensitive attributes to hide if necessary
    ];

    protected $casts = [
        'price' => 'double',  // Cast price to double
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'pos_system' => 'boolean',
        'self_delivery' => 'boolean',
        'mobile_app' => 'boolean',
        'review_system' => 'boolean',
        'chat_support' => 'boolean',
        'status' => 'integer',
    ];

    public function related_translations()
    {
        return $this->hasMany(Translation::class, 'translatable_id')
            ->where('translatable_type', self::class);
    }

}
