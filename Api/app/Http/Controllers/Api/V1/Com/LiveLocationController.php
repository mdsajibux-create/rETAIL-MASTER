<?php

namespace App\Http\Controllers\Api\V1\Com;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\LiveLocationResource;
use App\Models\LiveLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Deliveryman\app\Models\DeliveryMan;
use Modules\Order\app\Models\Order;

class LiveLocationController extends Controller
{
    /**
     * Update or create a live location for a trackable entity.
     */
    public function updateLocation(Request $request)
    {
        if (!auth('api')->check()) {
            return unauthorized_response();
        }

        $validator = Validator::make($request->all(), [
            'trackable_type' => 'required|string|in:deliveryman',
            'trackable_id' => 'required|integer',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'order_id' => 'required|array',
            'order_id.*' => 'integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $trackableType = null;
        if ($request->trackable_type === 'deliveryman') {
            $trackableType = DeliveryMan::class;
        }

        foreach ($request->order_id as $orderId) {
            $this->orderStatus($orderId); // Optional - depends on your logic

            LiveLocation::updateOrCreate(
                [
                    'order_id' => $orderId,
                ],
                [
                    'trackable_type' => $trackableType,
                    'trackable_id' => $request->trackable_id,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'last_updated' => now(),
                ]
            );
        }

        return response()->json([
            'message' => __('messages.update_success', ['name' => 'Location']),
        ]);
    }

    /**
     * Show the current live location of a trackable entity.
     */
    public function trackOrder(Request $request)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $this->orderStatus($request->order_id);
        $location = LiveLocation::where('order_id', $request->order_id)
            ->first();

        if (!$location) {
            return response()->json([
                'message' => __('messages.data_not_found'),
            ]);
        }

        return response()->json([
            'data' => new LiveLocationResource($location),
        ]);
    }

    private function orderStatus($order_id)
    {
        $order = Order::where('id', $order_id)->first();
        if ($order->cancelled_at !== null) {
            return response()->json([
                'message' => __('messages.order_already_cancelled')
            ]);
        }
        if ($order->status != 'shipped') {
            return response()->json([
                'message' => __('messages.order_is_not_shipped')
            ]);
        }
        if ($order->status == 'delivered') {
            return response()->json([
                'message' => __('messages.order_already_delivered')
            ]);
        }
        return true;
    }
}
