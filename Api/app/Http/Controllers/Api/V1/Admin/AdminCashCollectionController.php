<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\OrderActivityType;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminCashCollectionResource;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderActivity;
use Modules\Order\app\Models\OrderDeliveryHistory;

class AdminCashCollectionController extends Controller
{
    public function collectCash(Request $request)
    {
        if ($request->isMethod('POST')) {
            $user = auth('api')->user();
            $store_level = $user->activity_scope == 'system_level';

            if (!$store_level) {
                return response()->json(['message' => __('messages.authorization_invalid')], 422);
            }

            $validator = Validator::make($request->all(), [
                'order_id' => 'required|exists:orders,id',
                'deliveryman_id' => 'required|exists:users,id',
                'reference' => 'nullable|string|max:500',
                'activity_value' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $order = Order::with(['orderDeliveryHistory'])->find($request->order_id);

            if (!$order || !$this->isOrderValidForCashCollection($order, $request->deliveryman_id)) {
                return response()->json(['message' => __('messages.order_does_not_belong_to_deliveryman')], 422);
            }

            if (!$this->isCashOnDelivery($order)) {
                return response()->json(['message' => __('messages.order_is_not_cash_on_delivery')], 422);
            }

            $cash_collection = OrderActivity::with('ref')
                ->where('order_id', $request->order_id)
                ->where('ref_id', $request->deliveryman_id)
                ->where('activity_type', OrderActivityType::CASH_COLLECTION->value)
                ->first();

            if (!$cash_collection) {
                return response()->json(['message' => __('messages.order_does_not_belong_to_delivery')], 422);
            }

            if ($request->activity_value > $cash_collection->activity_value) {
                return response()->json(['message' => __('messages.received_amount_can\'t_be_greater')], 422);
            }

            $remainingAmount = $this->remainingAmount($order);
            if ($remainingAmount < $request->activity_value) {
                return response()->json(['message' => __('messages.total_amount_exceed', ['remainingAmount' => $remainingAmount])], 422);
            }

            $orderActivity = OrderActivity::create([
                'order_id' => $request->order_id,
                'ref_id' => $request->deliveryman_id,
                'reference' => $request->reference ?? null,
                'collected_by' => $user->id,
                'activity_from' => 'deliveryman',
                'activity_type' => OrderActivityType::CASH_DEPOSIT->value,
                'activity_value' => $request->activity_value
            ]);

            return response()->json([
                'message' => $orderActivity
                    ? __('messages.save_success', ['name' => 'Cash Deposit'])
                    : __('messages.save_failed', ['name' => 'Cash Deposit'])
            ], $orderActivity ? 201 : 500);
        }

        $cash_collection = OrderActivity::with('ref')->where('activity_type', OrderActivityType::CASH_COLLECTION->value)
            ->latest()
            ->paginate(10);

        return response()->json([
            'data' => AdminCashCollectionResource::collection($cash_collection),
            'meta' => new PaginationResource($cash_collection),
        ]);
    }

    private function isOrderValidForCashCollection($order, $deliverymanId)
    {
        return OrderDeliveryHistory::where('order_id', $order->id)
            ->where('deliveryman_id', $deliverymanId)
            ->where('status', 'delivered')
            ->exists();
    }

    private function isCashOnDelivery($order)
    {
        return $order->orderMaster->payment_gateway == 'cash_on_delivery';
    }

    private function remainingAmount($order)
    {
        $totalCollected = OrderActivity::where('order_id', $order->id)
            ->where('activity_type', OrderActivityType::CASH_COLLECTION->value)
            ->sum('activity_value');

        $totalDeposited = OrderActivity::where('order_id', $order->id)
            ->where('activity_type', OrderActivityType::CASH_DEPOSIT->value)
            ->sum('activity_value');

        return $totalCollected - $totalDeposited;
    }


}
