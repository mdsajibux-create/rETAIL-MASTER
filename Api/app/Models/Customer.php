<?php

namespace App\Models;

use App\Traits\HasMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Blog\app\Models\BlogComment;
use Modules\Blog\app\Models\BlogCommentReaction;
use Modules\Chat\app\Models\Chat;
use Modules\Chat\app\Models\ChatMessage;
use Modules\Feedback\app\Models\Review;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Models\ProductQuery;
use Modules\SmsGateway\app\Models\UserOtp;
use Modules\SupportTicket\app\Models\Ticket;
use Modules\SystemCore\app\Models\Media;
use Modules\Wallet\app\Models\Wallet;

class Customer extends Authenticatable // Extend Authenticatable instead of Model
{
    use HasApiTokens, SoftDeletes,HasMedia;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'image',
        'birth_day',
        'gender',
        'verified',
        'verify_method',
        'marketing_email',
        'marketing_sms',
        'firebase_token',
        'fcm_token',
        'google_id',
        'facebook_id',
        'apple_id',
        'status',
        'online_at',
        'def_lang',
        'password_changed_at',
        'email_verify_token',
        'email_verified',
        'email_verified_at',
        'deactivated_at'
    ];


    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isActive(): bool
    {
        return $this->status === 1 && $this->deleted_at === null;
    }

    public function scopeIsActive($query)
    {
        return $query->where('status', 1)->whereNull('deleted_at');
    }

    public function isDeactivated(): bool
    {
        return $this->status === 0;
    }

    public function isSuspended(): bool
    {
        return $this->status === 2;
    }

    public function deleteAccount(): bool
    {
        return $this->delete();
    }

    public function forceDeleteAccount(): bool
    {
        return $this->forceDelete();
    }

    public function restoreAccount(): bool
    {
        return $this->restore();
    }

    public function defaultAddress()
    {
        return $this->hasOne(CustomerAddress::class)->where('is_default', 1);
    }

    public function chats()
    {
        return $this->morphMany(Chat::class, 'user');
    }

    public function sentMessages()
    {
        return $this->morphMany(ChatMessage::class, 'sender');
    }

    public function receivedMessages()
    {
        return $this->morphMany(ChatMessage::class, 'receiver');
    }

    public function media()
    {
        return $this->morphMany(Media::class, 'fileable', 'user_type', 'user_id');
    }

    public function blogComments()
    {
        return $this->hasMany(BlogComment::class, 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function productQueries()
    {
        return $this->hasMany(ProductQuery::class, 'customer_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'customer_id');
    }

    public function blogCommentReactions()
    {
        return $this->hasMany(BlogCommentReaction::class, 'user_id');
    }

    public function reviewReactions()
    {
        return $this->hasMany(ReviewReaction::class, 'user_id');
    }

    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'owner');
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function userOtps()
    {
        return $this->hasMany(UserOtp::class, 'user_id')->where('user_type', 'customer');
    }

    public function notifications()
    {
        return $this->hasMany(UniversalNotification::class, 'notifiable_id')->where('notifiable_type', 'customer');
    }

    public function hasRunningOrders(): bool
    {
        return Order::where('customer_id', $this->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->exists();
    }
}
