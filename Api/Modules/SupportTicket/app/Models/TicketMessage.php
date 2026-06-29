<?php

namespace Modules\SupportTicket\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'sender_id',
        'receiver_id',
        'sender_role',
        'receiver_role',
        'message',
        'file',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function sender()
    {
        return $this->morphTo();
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

}
