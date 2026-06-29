<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderGrowthSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $orderData = collect($this->resource);

        // Initialize result array
        $growthData = [];
        $previousMonthOrders = 0;

        // Loop through months (1 to 12)
        for ($month = 1; $month <= 12; $month++) {
            $currentMonthOrders = $orderData->get($month, 0);

            // Handle growth calculation
            $growthPercentage = 0;
            if ($previousMonthOrders == 0 && $currentMonthOrders > 0) {
                $growthPercentage = 100;
            } elseif ($previousMonthOrders > 0) {
                // Regular growth calculation
                $growthPercentage = round((($currentMonthOrders - $previousMonthOrders) / $previousMonthOrders) * 100, 2);
            }

            $growthData[] = [
                'month' => date("F", mktime(0, 0, 0, $month, 1)),
                'orders' => $currentMonthOrders,
                'growth' => $growthPercentage
            ];

            // Update previous month orders for next iteration
            $previousMonthOrders = $currentMonthOrders;
        }

        return $growthData;
    }
}
