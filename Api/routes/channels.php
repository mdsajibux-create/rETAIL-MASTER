<?php

use Illuminate\Support\Facades\Broadcast;


// (Optional) Default Laravel Echo channel for user presence/auth if needed
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


// Private channel for live chat
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (int) $user->id === (int) $receiverId;
});
