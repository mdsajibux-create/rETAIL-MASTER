<?php

namespace App\Http\Resources\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminTransactionDashboardReportResource extends JsonResource
{
    protected $query;

    public function __construct(Builder $query)
    {
        parent::__construct(null);
        $this->query = $query;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $total_transactions_amount = (clone $this->query)
            ->where('status', 'delivered')
            ->whereNull('refund_status')
            ->where('payment_status', 'paid')
            ->sum('order_amount');

        $total_refund_amount = (clone $this->query)->where('refund_status', 'refunded')->sum('order_amount');

        $deliveryman_earnings = (clone $this->query)
            ->where('status', 'delivered')
            ->whereNull('refund_status')
            ->where('payment_status', 'paid')
            ->sum('delivery_charge');

        return [
            'total_transactions_amount' => $this->buildItem('pending-icon', 'Total Transaction Amount', round($total_transactions_amount)),
            'total_refund_amount' => $this->buildItem('pending-icon', 'Total Refund Amount', $total_refund_amount),
            'deliveryman_earnings' => $this->buildItem('pending-icon', 'Deliveryman Earnings', round($deliveryman_earnings)),
        ];
    }

    private function buildItem(string $icon, string $title, $count): array
    {
        return [
            'icon' => $icon,
            'title' => $title,
            'count' => $count
        ];
    }
}
