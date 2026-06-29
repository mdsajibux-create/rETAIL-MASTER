<?php

namespace Modules\Product\app\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\app\Transformers\ProductStockTransferItemResource;

class ProductStockTransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'reference_number'   => $this->reference_number,
            'type'               => $this->type,
            'status'             => $this->status,

            // ── Branches ─────────────────────────────────────
            'from_branch'        => $this->whenLoaded('fromBranch', fn() => [
                'id'   => $this->fromBranch->id,
                'name' => $this->fromBranch->name,
            ]),
            'to_branch'          => $this->whenLoaded('toBranch', fn() => [
                'id'   => $this->toBranch->id,
                'name' => $this->toBranch->name,
            ]),

            // ── Items ─────────────────────────────────────────
            'items'              => ProductStockTransferItemResource::collection(
                $this->whenLoaded('items')
            ),
            'items_count'        => $this->whenCounted('items'),

            // ── References ───────────────────────────────────
            'order_id'           => $this->order_id,
            'purchase_order_id'  => $this->purchase_order_id,
            'supplier_reference' => $this->supplier_reference,

            // ── People ───────────────────────────────────────
            'requested_by'       => $this->whenLoaded('requestedBy', fn() => [
                'id'   => $this->requestedBy->id,
                'name' => $this->requestedBy->name,
            ]),
            'approved_by'        => $this->whenLoaded('approvedBy', fn() => [
                'id'   => $this->approvedBy->id,
                'name' => $this->approvedBy->name,
            ]),
            'dispatched_by'      => $this->whenLoaded('dispatchedBy', fn() => [
                'id'   => $this->dispatchedBy->id,
                'name' => $this->dispatchedBy->name,
            ]),
            'received_by'        => $this->whenLoaded('receivedBy', fn() => [
                'id'   => $this->receivedBy->id,
                'name' => $this->receivedBy->name,
            ]),

            // ── Notes ────────────────────────────────────────
            'notes'              => $this->notes,
            'reason'             => $this->reason,
            'rejection_reason'   => $this->rejection_reason,

            // ── Timestamps ───────────────────────────────────
            'approved_at'        => $this->approved_at?->toDateTimeString(),
            'dispatched_at'      => $this->dispatched_at?->toDateTimeString(),
            'received_at'        => $this->received_at?->toDateTimeString(),
            'expected_at'        => $this->expected_at?->toDateTimeString(),
            'created_at'         => $this->created_at?->toDateTimeString(),
            'updated_at'         => $this->updated_at?->toDateTimeString(),
        ];
    }
}
