<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\OrderRefundInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\OrderRefund;
use Modules\Order\app\Transformers\OrderRefundRequestResource;

class BranchOrderRefundManageController extends Controller
{
    public function __construct(protected OrderRefundInterface $orderRefundRepo)
    {

    }

    public function orderRefundRequests(Request $request)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }

        $branch_id = auth('api')->user()->branch_id;

        $filters = [
            "status" => $request->status,
            "search" => $request->search,
            "order_refund_reason_id" => $request->order_refund_reason_id,
            "per_page" => $request->per_page,
        ];

        $requests = $this->orderRefundRepo->get_branch_order_refund_request($branch_id, $filters);

        return response()->json([
            'data' => OrderRefundRequestResource::collection($requests),
            'meta' => new PaginationResource($requests)
        ], 200);
    }

    public function handleRefundRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:order_refunds,id',
            'status' => 'required|in:approved,rejected,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $refund = OrderRefund::find($request->id);

        if ($refund->status == 'approved' && $request->status == 'rejected') {
            return response()->json([
                'message' => __('messages.already_approved', ['name' => 'Order Refund'])
            ]);
        }

        if ($refund->status == 'rejected' && $request->status == 'approved') {
            return response()->json([
                'message' => __('messages.already_rejected', ['name' => 'Order Refund'])
            ]);
        }

        if ($request->status === 'approved') {

            $success = $this->orderRefundRepo->approve_refund_request($request->id, $request->status);

            if ($success) {
                return response()->json([
                    'message' => __('messages.approve.success', ['name' => 'Order Refund Request']),
                ], 200);
            } else {
                return response()->json([
                    'message' => __('messages.approve.failed', ['name' => 'Order Refund Request']),
                ], 500);
            }
        }

        if ($request->status === 'rejected') {
            $validator = Validator::make($request->all(), [
                'reject_reason' => 'required|string|max:500'
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }
            $success = $this->orderRefundRepo->reject_refund_request($request->id, $request->status, $request->reject_reason);
            if ($success) {
                return response()->json([
                    'message' => __('messages.reject.success', ['name' => 'Order Refund Request']),
                ], 200);
            } else {
                return response()->json([
                    'message' => __('messages.reject.failed', ['name' => 'Order Refund Request']),
                ], 500);
            }
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Order Refund Request']),
            ], 500);
        }
    }
}
