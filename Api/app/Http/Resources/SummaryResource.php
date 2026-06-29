<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'summary' => [
                'product' => $this->buildItem('product-icon', 'Total Product', $this->total_products),
                'order' => $this->buildItem('order-icon', 'Total Order', $this->total_orders),
                'customer' => $this->buildItem('customer-icon', 'Total Customer', $this->total_customers),
                'deliveryman' => $this->buildItem('deliveryman-icon', 'Total Deliverymen', $this->total_deliverymen),
                'category' => $this->buildItem('category-icon', 'Total Categories', $this->total_categories),
                'brand' => $this->buildItem('brand-icon', 'Total Brands', $this->total_brands),
                'coupon' => $this->buildItem('coupon-icon', 'Total Coupons', $this->total_coupons),
                'blog' => $this->buildItem('blog-icon', 'Total Blogs', $this->total_blogs),
                'ticket' => $this->buildItem('ticket-icon', 'Total Tickets', $this->total_tickets),
            ],
            'order_summary' => [
                'pending_orders' => $this->buildItem('pending-icon', 'Pending Orders', $this->total_pending_orders),
                'completed_orders' => $this->buildItem('completed-icon', 'Completed Orders', $this->total_delivered_orders),
                'cancelled_orders' => $this->buildItem('cancelled-icon', 'Cancelled Orders', $this->total_cancelled_orders),
                'unassigned_orders' => $this->buildItem('unassigned-icon', 'Unassigned Orders', $this->deliveryman_not_assigned_orders),
                'refunded_orders' => $this->buildItem('refunded-icon', 'Refunded Orders', $this->total_refunded_orders),
            ],
            'financial_summary' => [
                'total_order_amount' => $this->buildItem('earnings-icon', 'Total Order Amount', $this->total_earnings),
                'total_refunds' => $this->buildItem('refunds-icon', 'Total Refunds', $this->total_refunds),
                'total_tax' => $this->buildItem('tax-icon', 'Total Tax', $this->total_tax),
                'total_withdrawals' => $this->buildItem('withdrawal-icon', 'Total Withdrawals', $this->total_withdrawals),
                'total_revenue' => $this->buildItem('revenue-icon', 'Total Revenue', $this->total_revenue),
                'total_pos_order_earnings' => $this->buildItem('pos_earnings-icon', 'Total Pos Sales', $this->total_pos_order_earnings)
            ],
        ];
    }

    /**
     * Helper method to build summary items.
     */
    private function buildItem(string $icon, string $title, $count): array
    {
        return [
            'icon' => $icon,
            'title' => $title,
            'count' => $count
        ];
    }
}
