<?php

namespace Modules\Product\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Branch\app\Models\Branch;

class ProductStockTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference_number',
        'type',
        'from_branch_id',
        'to_branch_id',
        'order_id',
        'purchase_order_id',
        'supplier_reference',
        'requested_by',
        'approved_by',
        'dispatched_by',
        'received_by',
        'notes',
        'reason',
        'rejection_reason',
        'status',
        'approved_at',
        'dispatched_at',
        'received_at',
        'expected_at',
        'qty_received',
        'qty_dispatched',
    ];

    protected $casts = [
        'approved_at'   => 'datetime',
        'dispatched_at' => 'datetime',
        'received_at'   => 'datetime',
        'expected_at'   => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function items()
    {
        return $this->hasMany(ProductStockTransferItem::class, 'product_stock_transfer_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function dispatchedBy()
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ── Auto-generate reference number ───────────────────────

    protected static function booted(): void
    {
        static::creating(function ($transfer) {
            $transfer->reference_number = self::generateReference();
        });
    }

    private static function generateReference(): string
    {
        $latest = self::withTrashed()->whereYear('created_at', now()->year)->count() + 1;
        return 'MOV-' . now()->year . '-' . str_pad($latest, 5, '0', STR_PAD_LEFT);
        // e.g. MOV-2025-00001
    }
}
