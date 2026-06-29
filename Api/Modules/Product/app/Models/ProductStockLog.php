<?php

namespace Modules\Product\app\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Branch\app\Models\Branch;

class ProductStockLog extends Model
{

    protected $fillable = [
        'branch_id',
        'product_id',
        'variant_id',
        'stock_id',
        'type',
        'qty_before',
        'qty_changed',
        'qty_after',
        'cost_price',
        'reason',
        'notes',
        'created_by',
        'qty_damaged',
    ];

    protected $casts = [
        'qty_before'  => 'integer',
        'qty_changed' => 'integer',
        'qty_after'   => 'integer',
        'cost_price'  => 'decimal:2',
    ];

    // ── Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
