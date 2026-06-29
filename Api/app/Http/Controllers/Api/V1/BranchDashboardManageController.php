<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\BranchOtherSummaryResource;
use App\Http\Resources\BranchSummaryResource;
use App\Http\Resources\OrderGrowthSummaryResource;
use App\Interfaces\BranchManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Fluent;

class BranchDashboardManageController extends Controller
{
    public function __construct(protected BranchManageInterface $storeRepo)
    {

    }

    public function summaryData(Request $request)
    {
        $validator = Validator::make(['slug' => $request->slug], [
            'slug' => 'nullable|exists:stores,slug',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filters = [
            "time_period" => $request->time_period ?? 'this_year',
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
        ];

        $summary       = $this->storeRepo->getSummaryData($request->slug);
        $salesSummary  = $this->storeRepo->getSalesSummaryData($filters, $request->slug);
        $otherSummary  = $this->storeRepo->getOtherSummaryData($request->slug);
        $orderGrowth   = $this->storeRepo->getOrderGrowthData($request->slug);


        return response()->json([
            'data' => [
                'count_data' => new BranchSummaryResource((object)$summary),
                'sales_summary' => $salesSummary
                    ? new BranchSummaryResource(new Fluent($salesSummary))
                    : null,
                'others_summary' => new BranchOtherSummaryResource(new Fluent($otherSummary)),
                'order_growth_summary' => $orderGrowth
                    ? new OrderGrowthSummaryResource(new Fluent($orderGrowth))
                    : null,
            ]
        ]);
    }

    public function summaryData33(Request $request)
    {
        $validator = Validator::make(['slug' => $request->slug], [
            'slug' => 'nullable|exists:stores,slug',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $this->storeRepo->getSummaryData($request->slug);

        return response()->json(new BranchSummaryResource((object)$data));
    }

    public function salesSummaryData(Request $request)
    {
        $filters = [
            "time_period" => $request->time_period,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
        ];

        $data = $this->storeRepo->getSalesSummaryData($filters, $request->slug);


        return response()->json(new BranchSummaryResource($data));
    }

    public function orderGrowthData(Request $request)
    {
        $data = $this->storeRepo->getOrderGrowthData($request->slug);

        return response()->json(new OrderGrowthSummaryResource($data));
    }

    public function otherSummaryData(Request $request)
    {
        $validator = Validator::make(['slug' => $request->slug], [
            'slug' => 'nullable|exists:stores,slug',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $this->storeRepo->getOtherSummaryData($request->slug);

        return response()->json(new BranchOtherSummaryResource((object)$data));
    }


}
