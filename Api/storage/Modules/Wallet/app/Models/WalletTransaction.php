<?php

namespace Modules\Wallet\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'transaction_ref',
        'transaction_details',
        'amount',
        'type',
        'purpose',
        'payment_gateway',
        'payment_status',
        'status',
    ];


    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

}
