<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactUsMessage extends Model
{
    protected $fillable = [
        "name",
        "email",
        "phone",
        "message",
        "reply",
        "replied_by",
        "replied_at",
        "status"
    ];

    public function repliedBy()
    {
        return $this->belongsTo(User::class, "replied_by");
    }
}
