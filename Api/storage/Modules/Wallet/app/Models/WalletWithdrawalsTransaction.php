<?php

namespace Modules\Wallet\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class WalletWithdrawalsTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id', 'owner_id', 'owner_type', 'withdraw_gateway_id', 'gateway_name', 'amount',
        'fee', 'gateways_options', 'details', 'approved_by',
        'approved_at', 'status', 'reject_reason', 'attachment'
    ];

    protected $casts = [
        'gateways_options' => 'array',
        'approved_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }
    public function owner()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


}
