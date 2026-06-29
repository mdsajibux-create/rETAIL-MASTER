<?php

namespace Modules\Chat\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_type',
    ];

    // The owner of the chat (customer, deliveryman, admin)
    public function user(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'user_type', 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }


}
