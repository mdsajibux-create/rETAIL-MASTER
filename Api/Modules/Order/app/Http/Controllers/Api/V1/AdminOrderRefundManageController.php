<?php

namespace Modules\Order\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\OrderRefundInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\OrderRefund;
use Modules\Order\app\Transformers\OrderRefundReasonDetailsResource;
use Modules\Order\app\Transformers\OrderRefundReasonResource;
use Modules\Order\app\Transformers\OrderRefundRequestResource;

class AdminOrderRefundManageController extends Controller
{
    public function __construct(protected OrderRefundInterface $orderRefundRepo)
    {

    }

    public function orderRefundRequest(Request $request)
    {
        $filters = [
            "status" => $request->status,
            "search" => $request->search,
            "order_refund_reason_id" => $request->order_refund_reason_id,
            "branch_id" => $request->branch_id,
            "per_page" => $request->per_page,
        ];

        $requests = $this->orderRefundRepo->get_order_refund_request($filters);

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
            'reject_reason' => 'nullable|string|max:500'
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
            if (!isset($request->reject_reason) || empty($request->reject_reason)) {
                return response()->json([
                    'message' => __('validation.required', ['attribute' => 'Reason']),
                ], 422);
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
        }
        if ($request->status === 'refunded') {
            $success = $this->orderRefundRepo->refunded_refund_request($request->id, $request->status);
            if ($success) {
                return response()->json([
                    'message' => __('messages.order_refund_success'),
                ], 200);
            } else {
                return response()->json([
                    'message' => __('messages.order_refund_failed'),
                ], 500);
            }
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Order Refund Request']),
            ], 500);
        }
    }


    public function allOrderRefundReason(Request $request)
    {
        $filters = [
            'per_page' => $request->per_page,
            'search' => $request->search,
        ];
        $reasons = $this->orderRefundRepo->order_refund_reason_list($filters);
        return response()->json([
            'data' => OrderRefundReasonResource::collection($reasons),
            'meta' => new PaginationResource($reasons)
        ], 200);
    }

    public function createOrderRefundReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $success = $this->orderRefundRepo->create_order_refund_reason($request->reason);
        createOrUpdateTranslation($request, $success, 'Modules\Order\app\Models\OrderRefundReason', $this->orderRefundRepo->translationKeys());
        if ($success) {
            return response()->json([
                'message' => __('messages.save_success', ['name' => 'Order Refund Reason']),
            ], 201);
        } else {
            return response()->json([
                'message' => __('messages.save_failed', ['name' => 'Order Refund Reason']),
            ], 500);
        }
    }

    public function updateOrderRefundReason(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:order_refund_reasons,id',
            'reason' => 'required|string|max:500',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $success = $this->orderRefundRepo->update_order_refund_reason($request->all());
        createOrUpdateTranslation($request, $success, 'Modules\Order\app\Models\OrderRefundReason', $this->orderRefundRepo->translationKeys());
        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Order Refund Reason']),
            ], 201);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Order Refund Reason']),
            ], 500);
        }
    }

    public function showOrderRefundReason(Request $request)
    {
        $validator = Validator::make(['id' => $request->id], [
            'id' => 'required|exists:order_refund_reasons,id',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $reason = $this->orderRefundRepo->get_order_refund_reason_by_id($request->id);
        if ($reason) {
            return response()->json(new OrderRefundReasonDetailsResource($reason), 200);
        } else {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }
    }

    public function deleteOrderRefundReason(int $id)
    {
        $success = $this->orderRefundRepo->delete_order_refund_reason($id);
        if ($success) {
            return response()->json([
                'message' => __('messages.delete_success', ['name' => 'Order Refund Reason']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.delete_failed', ['name' => 'Order Refund Reason']),
            ], 500);
        }
    }
}
