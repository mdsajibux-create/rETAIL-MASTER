<?php

namespace Modules\Chat\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatMessage extends Model
{
    use HasFactory,SoftDeletes;

      protected $table = 'chat_messages';

      protected $fillable = [
          'chat_id',
          'receiver_chat_id',
          'sender_id',
          'sender_type',
          'receiver_id',
          'receiver_type',
          'message',
          'file',
      ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'sender_type', 'sender_id');
    }

    public function receiver(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'receiver_type', 'receiver_id');
    }

}
