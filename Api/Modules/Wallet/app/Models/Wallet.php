<?php

namespace Modules\Wallet\app\Models;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use  SoftDeletes;

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'owner_id', // Foreign key for polymorphic relation
        'owner_type', // The type of the related model (User, Customer)
        'balance',
        'earnings',
        'withdrawn',
        'refunds',
        'status'
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'balance' => 0,
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Define the polymorphic relationship to User or Customer.
     */
    public function owner()
    {
        return $this->morphTo();
    }

    /**
     * Define the relationship to WalletTransaction.
     */
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Check if the wallet is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
    public function scopeForOwner($query, $owner)
    {
        if (is_null($owner)) {
            return $query->whereRaw('1 = 0');
        }
        if (!in_array(get_class($owner), [User::class, Customer::class])) {
            throw new \InvalidArgumentException('Invalid owner type.');
        }
        return $query->where('owner_id', $owner->id)->where('owner_type', get_class($owner));
    }

}
