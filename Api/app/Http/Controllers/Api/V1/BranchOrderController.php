<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Admin\AdminOrderStatusResource;
use App\Http\Resources\Com\OrderPaymentTrackingResource;
use App\Http\Resources\Com\OrderRefundTrackingResource;
use App\Http\Resources\Com\OrderTrackingResource;
use App\Http\Resources\Com\PaginationResource;
use App\Jobs\DispatchOrderEmails;
use App\Services\Order\OrderManageNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderDetail;
use Modules\Order\app\Transformers\BranchOrderResource;
use Modules\Order\app\Transformers\InvoiceResource;
use Modules\Order\app\Transformers\OrderRefundRequestResource;
use Modules\Order\app\Transformers\OrderSummaryResource;
use Modules\Product\app\Models\FlashSale;
use Modules\Product\app\Models\ProductStock;

class BranchOrderController extends Controller
{

    protected OrderManageNotificationService $orderManageNotificationService;
    protected bool $canChangeOrderStatus;

    public function __construct(OrderManageNotificationService $orderManageNotificationService)
    {
        $this->orderManageNotificationService = $orderManageNotificationService;
        $this->canChangeOrderStatus = true;
    }

    public function allOrders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->order_id) {
            $order = Order::with([
                'customer',
                'orderDetail.product',
                'deliveryman',
                'shippingAddress'
            ])
                ->where('id', $request->order_id)
                ->first();

            if (!$order) {
                return response()->json([
                    'message' => __('message.data_not_found'),
                ], 404);
            }


            return response()->json([
                'order_data' => new BranchOrderResource($order),
                'order_summary' => new OrderSummaryResource($order),
                'refund' => $order->refund ? new OrderRefundRequestResource($order->refund) : null,
                'order_tracking' => OrderTrackingResource::collection(
                    $order->orderActivities
                        ->where('activity_type', 'order_status')
                        ->sortByDesc('created_at') // Sort latest first
                        ->unique('activity_value') // Keep only latest per status
                        ->values() // Reset collection keys
                ),
                'order_payment_tracking' => OrderPaymentTrackingResource::collection(
                    $order->orderActivities
                        ->where('activity_type', 'payment_status')
                        ->sortByDesc('created_at') // Sort latest first
                        ->unique('activity_value') // Keep only latest per status
                        ->values() // Reset collection keys
                ),
                'order_refund_tracking' => OrderRefundTrackingResource::collection(
                    $order->orderActivities
                        ->where('activity_type', 'refund_status')
                        ->sortByDesc('created_at') // Sort latest first
                        ->unique('activity_value') // Keep only latest per status
                        ->values() // Reset collection keys
                ),
            ]);
        }


            $branch_id = auth('api')->user()->branch_id;

            // Get order info
            $orders = Order::with([
                'customer',
                'orderDetail.product',
                'deliveryman',
                'shippingAddress'
            ])->where('order_type', '!=', 'pos')
                ->where('branch_id', $branch_id);

            // Apply status filter
            if (isset($request->status)) {
                $orders->where('status', $request->status);
            }
            if (isset($request->start_date) && isset($request->end_date)) {
                $orders->whereBetween('created_at', [$request->start_date, $request->end_date]);
            }

            // Apply payment_status filter
            if (isset($request->payment_status)) {
                $orders->where('payment_status', $request->payment_status);
            }

            $orders->when($request->search, fn($query) => $query
                ->where('id', 'LIKE', '%' . $request->search . '%')
                ->orWhere('invoice_number', 'LIKE', '%' . $request->search . '%'));

            $orders = $orders->orderBy('created_at', 'desc')
                ->paginate($request->per_page ?? 10);

            $orderStatusCounts = new AdminOrderStatusResource(Order::where('branch_id', $branch_id)->get());

            return response()->json([
                'orders' => BranchOrderResource::collection($orders),
                'meta' => new PaginationResource($orders),
                'status' => $orderStatusCounts,
            ]);

    }

    public function orderInvoice(Request $request)
    {
        $order = Order::with([
            'customer',
            'orderDetail.product',
            'deliveryman',
            'shippingAddress'
        ])
            ->where('id', $request->order_id)
            ->first();

        if (!$order) {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }


        // InvoiceResource expects customer/shipping_address directly
        $order->setRelation('customer', $order->customer);
        $order->setRelation('shipping_address', $order->shippingAddress);

        if (!$order) {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }

        return response()->json(new InvoiceResource($order));
    }

    public function changeOrderStatus(Request $request)
    {
        if (!$this->canChangeOrderStatus) {
            return response()->json(['message' => __('messages.order_status_not_changeable')], 422);
        }
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,confirmed,processing,pickup,shipped'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        $statusFlow = [
            'pending',
            'confirmed',
            'processing',
            'pickup',
            'shipped',
        ];

        $currentIndex = array_search($order->status, $statusFlow);
        $newIndex = array_search($request->status, $statusFlow);

        if ($newIndex === false || $newIndex < $currentIndex || $order->status === $request->status) {
            return response()->json(['message' => __('messages.order_status_not_changeable')], 422);
        }

        $order->status = $request->status;
        $success = $order->save();

        // Notify seller and customer
        $order = [$order->id];
        $this->orderManageNotificationService->createOrderNotification($order, 'seller_order_status_pcpps');

        try {
            // Dispatch the email job asynchronously
            dispatch(new DispatchOrderEmails($order->id));
        } catch (\Exception $e) {
        }

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Order status'])
            ]);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Order status'])
            ], 500);
        }


    }

    public function cancelOrder(Request $request)
    {
        if (!$this->canChangeOrderStatus) {
            return response()->json(['message' => __('messages.order_status_not_changeable')], 422);
        }

        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        $order = Order::find($request->order_id);

        if (!$order) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }


        // If the order is once shipped or cancelled or on_hold or delivered the order status can not be cancelled
        if ($order->status === 'shipped' || $order->status === 'cancelled' || $order->status === 'on_hold' || $order->status === 'delivered') {
            return response()->json(['message' => __('messages.order_status_not_changeable')], 422);
        }

        $order->cancelled_by = auth('api')->user()->id;
        $order->cancelled_at = Carbon::now();
        $order->status = 'cancelled';
        $success = $order->save();


        if ($success) {
            //  Restore stock for each order item ──────────────────────────
            $orderDetails = OrderDetail::where('order_id', $order->id)->get();
            foreach ($orderDetails as $detail) {
                ProductStock::where('variant_id', $detail->variant_id ?? null)
                    ->where('branch_id', $order->branch_id)
                    ->increment('qty', $detail->quantity);

                // Restore flash sale purchase limit if applied
                if ($detail->product_campaign_id) {
                    FlashSale::where('id', $detail->product_campaign_id)->increment('purchase_limit', $detail->quantity);
                }
            }

            // Notify seller and customer
            $order = [$order->id];
            $this->orderManageNotificationService->createOrderNotification($order, 'branch_order_cancelled');

            return response()->json([
                'message' => __('messages.order_cancel_successful')
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.order_cancel_failed')
            ], 500);
        }
    }
}
