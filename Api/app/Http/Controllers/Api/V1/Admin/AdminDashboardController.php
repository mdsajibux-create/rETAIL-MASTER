<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminOtherSummaryResource;
use App\Http\Resources\OrderGrowthSummaryResource;
use App\Http\Resources\SalesSummaryResource;
use App\Http\Resources\SummaryResource;
use App\Interfaces\AdminDashboardInterface;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(protected AdminDashboardInterface $adminRepo)
    {

    }

    public function summaryData(Request $request)
    {
        $filters_two = [
            'product_type' => $request->product_type
        ];

        $get_summary_data = $this->adminRepo->getSummaryData(null, $filters_two);

        $filters = [
            "product_type" => $request->product_type,
            "this_week" => $request->this_week,
            "this_month" => $request->this_month,
            "this_year" => $request->this_year,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
        ];

        $get_sales_summary_data = $this->adminRepo->getSalesSummaryData($filters);
        $get_others_summary_data = $this->adminRepo->getOtherSummaryData($filters_two);
        $get_order_growth_summary_data = $this->adminRepo->getOrderGrowthData($filters_two);

        return response()->json([
            'data' => [
                'count_data' => new SummaryResource((object)$get_summary_data),
                'sales_summary' => new SalesSummaryResource($get_sales_summary_data),
                'others_summary' => new AdminOtherSummaryResource((object)$get_others_summary_data),
                'order_growth_summary' => new OrderGrowthSummaryResource($get_order_growth_summary_data),
            ]
        ]);

    }

    public function salesSummaryData(Request $request)
    {
        $filters = [
            "this_week" => $request->this_week,
            "this_month" => $request->this_month,
            "this_year" => $request->this_year,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
        ];

        $data = $this->adminRepo->getSalesSummaryData($filters);

        return response()->json(new SalesSummaryResource($data));
    }

    public function orderGrowthData(Request $request)
    {
        $filters = [];

        $data = $this->adminRepo->getOrderGrowthData($filters);

        return response()->json(new OrderGrowthSummaryResource($data));
    }

    public function otherSummaryData(Request $request)
    {
        $filters = [];

        $data = $this->adminRepo->getOtherSummaryData($filters);

        return response()->json(new AdminOtherSummaryResource((object)$data));

    }
}
