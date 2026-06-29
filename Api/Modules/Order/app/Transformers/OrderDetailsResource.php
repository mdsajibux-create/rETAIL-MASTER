<?php

namespace Modules\Order\app\Transformers;

use App\Actions\ImageModifier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\app\Models\Product;

class OrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'product_image_url' => ImageModifier::generateImageUrl($this->product?->image),
            'behaviour' => $this->behaviour, // service, digital, consumable, combo
            'product_sku' => $this->product_sku,
            'variant_details' => json_decode($this->variant_details),
            'product_campaign_id' => $this->product_campaign_id,
            'base_price' => $this->base_price ?? 0,
            'discount_type' => $this->discount_type ?? null,
            'discount_rate' => $this->discount_rate ?? 0,
            'discount_amount' => $this->discount_amount,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'line_total_price_with_qty' => $this->line_total_price_with_qty,
            'coupon_discount_amount' => $this->coupon_discount_amount,
            'line_total_excluding_tax' => $this->line_total_excluding_tax,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total_tax_amount' => $this->total_tax_amount,
            'line_total_price' => $this->line_total_price,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'review_status' => auth('api_customer')->check() && $this->order?->isReviewedByCustomer(
                    auth('api_customer')->user()->id,
                    $this->order_id,
                    $this->product_id,
                    Product::class),
        ];
    }
}
