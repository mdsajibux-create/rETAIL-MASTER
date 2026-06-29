<?php

namespace App\Http\Controllers\Api\V1\Deliveryman;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Deliveryman\DeliverymanMyOrdersResource;
use App\Http\Resources\Deliveryman\DeliverymanOrderRequestResource;
use App\Interfaces\DeliverymanManageInterface;
use App\Services\Order\OrderManageNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Transformers\AdminOrderResource;
use Modules\Order\app\Transformers\OrderSummaryResource;

class DeliverymanOrderManageController extends Controller
{

    protected OrderManageNotificationService $orderManageNotificationService;
    protected DeliverymanManageInterface $deliverymanRepo;

    public function __construct(
        DeliverymanManageInterface $deliverymanRepo,
        OrderManageNotificationService $orderManageNotificationService
    ) {
        $this->deliverymanRepo = $deliverymanRepo;
        $this->orderManageNotificationService = $orderManageNotificationService;
    }

    public function getMyOrders(Request $request)
    {
        $order_id = $request->order_id;
        if (!auth('api')->user() && auth('api')->user()->activity_scope !== 'delivery_level') {
            unauthorized_response();
        }

        if ($order_id) {
            $order = $this->deliverymanRepo->deliverymanOrderDetails($order_id);

            if ($order) {
                return response()->json(
                    [
                        'order_data' => new AdminOrderResource($order),
                        'order_summary' => new OrderSummaryResource($order),
                    ]
                );
            } else {
                return response()->json(['message' => __('messages.data_not_found')], 404);
            }

        }

        $validator = Validator::make($request->all(), [
            'status' => 'nullable|in:accepted'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $filters = [
            'status' => $request->status
        ];

        $orders = $this->deliverymanRepo->deliverymanOrders($filters);

        if ($orders) {
            return response()->json([
                'message' => __('messages.data_found'),
                'data' => DeliverymanMyOrdersResource::collection($orders),
                'meta' => new PaginationResource($orders)
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function getOrderRequest()
    {
        if (!auth('api')->user() && auth('api')->user()->activity_scope != 'delivery_level') {
            unauthorized_response();
        }

        $order_requests = $this->deliverymanRepo->orderRequests();

        if (!$order_requests) {
            return response()->json([
                'message' => __('messages.data_not_found'),
                'data' => [],
            ]);
        } else {
            return response()->json([
                'message' => __('messages.data_found'),
                'data' => DeliverymanOrderRequestResource::collection($order_requests),
                'meta' => new PaginationResource($order_requests)
            ]);
        }
    }

    public function handleOrderRequest(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
            'status' => 'required|in:accepted,ignored',
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $deliveryman = auth('api')->user();

        if (!$deliveryman || $deliveryman->activity_scope !== 'delivery_level') {
            unauthorized_response();
        }

        $limit = 2;
        $activeOrders = Order::with(['orderMaster.orderAddress', 'store'])
            ->whereNotIn('status', ['delivered', 'cancelled', 'on_hold']) // Exclude both delivered and cancelled
            ->whereHas('orderDeliveryHistory', function ($query) use ($deliveryman) {
                $query->where('deliveryman_id', $deliveryman->id)
                    ->where('status', 'accepted'); // Ensure it's accepted
            })
            ->latest()
            ->get();

        if ($request->status === 'accepted' && $activeOrders->count() > $limit) {
            return response()->json([
                'message' => __('messages.order_accept_limit_reached', ['limit' => $limit]),
            ]);
        }

        // update order delivery history
        $success = $this->deliverymanRepo->updateOrderStatus(
            $request->status,
            $request->id,
            $request->reason
        );

        if ($success === 'accepted') {
            return response()->json([
                'message' => __('messages.deliveryman_order_request_accept_successful')
            ], 200);
        } elseif ($success === 'already confirmed') {
            return response()->json([
                'message' => __('messages.deliveryman_order_already_taken')
            ], 422);
        } elseif ($success === 'already accepted') {
            return response()->json([
                'message' => __('messages.deliveryman_order_already_accepted')
            ], 422);
        } elseif ($success === 'ignored') {
            return response()->json([
                'message' => __('messages.deliveryman_order_request_ignore_successful')
            ], 200);
        } elseif ($success === 'already ignored') {
            return response()->json([
                'message' => __('messages.deliveryman_order_already_ignored')
            ], 422);
        } elseif ($success === 'reason is required') {
            return response()->json([
                'message' => __('validation.required', ["attribute" => "Reason"])
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:orders,id',
            'status' => 'required|in:pickup,shipped,delivered',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $deliveryman = auth('api')->user();

        if (!$deliveryman || $deliveryman->activity_scope !== 'delivery_level') {
            unauthorized_response();
        }

        $already_cancelled = Order::with('orderDeliveryHistory')
            ->whereHas('orderDeliveryHistory', function ($query) use ($deliveryman, $request) {
                $query->where('deliveryman_id', $deliveryman->id)
                    ->where('order_id', $request->id)
                    ->where('status', 'cancelled'); // Ensures status is NOT 'accepted'
            })
            ->exists();

        $already_ignored = Order::with('orderDeliveryHistory')
            ->whereHas('orderDeliveryHistory', function ($query) use ($deliveryman, $request) {
                $query->where('deliveryman_id', $deliveryman->id)
                    ->where('order_id', $request->id)
                    ->where('status', 'ignored'); // Ensures status is NOT 'accepted'
            })
            ->exists();

        $already_delivered = Order::with('orderDeliveryHistory')
            ->whereHas('orderDeliveryHistory', function ($query) use ($deliveryman, $request) {
                $query->where('deliveryman_id', $deliveryman->id)
                    ->where('order_id', $request->id)
                    ->where('status', 'delivered'); // Ensures status is NOT 'accepted'
            })
            ->exists();

        if ($already_cancelled) {
            return response()->json([
                'message' => __('messages.order_already_cancelled')
            ], 422);
        }
        if ($already_ignored) {
            return response()->json([
                'message' => __('messages.order_already_ignored')
            ], 422);
        }
        if ($already_delivered) {
            return response()->json([
                'message' => __('messages.order_already_delivered')
            ], 422);
        }

        $order = Order::with(
            'customer',
            'orderAddress',
            'deliveryman'
        )->find($request->id);
        $statusFlow = [
            'pickup',
            'shipped',
            'delivered'
        ];

        $currentIndex = array_search($order->status, $statusFlow);
        $newIndex = array_search($request->status, $statusFlow);

        if ($newIndex === false || $newIndex < $currentIndex || $order->status === $request->status) {
            return response()->json([
                'message' => __('messages.order_status_not_changeable')
            ], 422);
        }

        // update order delivery history
        $success = $this->deliverymanRepo->orderChangeStatus($request->status, $request->id);

        if ($success === 'order_is_not_accepted') {
            return response()->json([
                'message' => __('messages.order_is_not_accepted')
            ]);
        }

        $this->orderManageNotificationService->createOrderNotification($order->id, 'deliveryman_order_status_psd');

        if ($success === 'delivered') {
            return response()->json([
                'message' => __('messages.order_delivered_success')
            ]);
        } elseif ($success === 'already delivered') {
            return response()->json([
                'message' => __('messages.deliveryman_order_already_taken')
            ], 422);
        } elseif ($success === 'pickup') {
            return response()->json([
                'message' => __('messages.order_pickup_success')
            ]);
        } elseif ($success === 'shipped') {
            return response()->json([
                'message' => __('messages.order_shipped_success')
            ]);
        } else {
            return response()->json([
                'message' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function orderDeliveryHistory()
    {
        $order_histories = $this->deliverymanRepo->deliverymanOrderHistory();

        if ($order_histories === 'unauthorized') {
            return response()->json([
                'message' => 'Unauthorized access. Please log in.',
            ], 401);
        }

        if ($order_histories->isEmpty()) {
            return response()->json([
                'message' => __('messages.no_order_history_found')
            ], 404);
        }

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => DeliverymanMyOrdersResource::collection($order_histories),
            'meta' => new PaginationResource($order_histories)
        ], 200);
    }
}
