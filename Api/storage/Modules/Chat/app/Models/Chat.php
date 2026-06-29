<?php

namespace Modules\Chat\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;


class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_type',
    ];

    // The owner of the chat (customer, deliveryman, admin, store.)
    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
