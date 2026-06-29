<?php

namespace Modules\Order\app\Http\Controllers\Api\V1;

use App\Enums\OrderActivityType;
use App\Enums\OrderStatusType;
use App\Enums\WalletOwnerType;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminOrderStatusResource;
use App\Http\Resources\Com\OrderPaymentTrackingResource;
use App\Http\Resources\Com\OrderRefundTrackingResource;
use App\Http\Resources\Com\OrderTrackingResource;
use App\Http\Resources\Com\PaginationResource;
use App\Mail\DynamicEmail;
use App\Services\Order\OrderManageNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderActivity;
use Modules\Order\app\Models\OrderDeliveryHistory;
use Modules\Order\app\Models\OrderDetail;
use Modules\Order\app\Transformers\AdminOrderResource;
use Modules\Order\app\Transformers\InvoiceResource;
use Modules\Order\app\Transformers\OrderRefundRequestResource;
use Modules\Order\app\Transformers\OrderSummaryResource;
use Modules\Product\app\Models\FlashSale;
use Modules\Product\app\Models\ProductStock;
use Modules\SystemCore\app\Models\EmailTemplate;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;

class AdminOrderManageController extends Controller
{
    protected $orderManageNotificationService;

    public function __construct(OrderManageNotificationService $orderManageNotificationService)
    {
        $this->orderManageNotificationService = $orderManageNotificationService;
    }

    public function allOrders(Request $request)
    {
        $order_id = $request->order_id;

        if ($order_id) {
            $order = Order::with([
                'customer',
                'orderDetail.product',
                'zone',
                'state',
                'city',
                'area',
                'deliveryman',
                'orderAddress',
                'refund',
                'refund.orderRefundReason',
                'orderActivities',
            ])
                ->where('id', $order_id)
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $deliveryman_id = $order->confirmed_by;
            $total_delivered = Order::where('confirmed_by', $deliveryman_id)->where('status', 'delivered')->count();

            $last_delivered_location = Order::with('shippingAddress')
                ->where('confirmed_by', $deliveryman_id)
                ->where('status', 'delivered')
                ->orderBy('delivery_completed_at', 'desc')
                ->first();

            if ($order->deliveryman) {
                $order->deliveryman->last_delivered_location = optional($last_delivered_location?->shippingAddress)->address ?? 'No address available';
                $order->deliveryman->total_delivered = $total_delivered ?? 0;
            }
            return response()->json(
                [
                    'order_data' => new AdminOrderResource($order),
                    'order_summary' => new OrderSummaryResource($order),
                    'refund' => $order->refund ? new OrderRefundRequestResource($order->refund) : null,
                    'order_tracking' => OrderTrackingResource::collection(
                        $order->orderActivities
                            ->where('activity_type', 'order_status')
                            ->sortByDesc('created_at')
                            ->unique('activity_value')
                            ->values()
                    ),
                    'order_payment_tracking' => OrderPaymentTrackingResource::collection(
                        $order->orderActivities
                            ->where('activity_type', 'payment_status')
                            ->sortByDesc('created_at')
                            ->unique('activity_value')
                            ->values()
                    ),
                    'order_refund_tracking' => OrderRefundTrackingResource::collection(
                        $order->orderActivities
                            ->where('activity_type', 'refund_status')
                            ->sortByDesc('created_at')
                            ->unique('activity_value')
                            ->values()
                    ),
                ]
            );
        }

        $ordersQuery = Order::with([
            'customer',
            'orderDetail',
            'deliveryman',
            'shippingAddress'
        ])->whereNot('order_type', 'pos');

        $ordersQuery->when($request->status, fn($query) => $query->where('status', $request->status));
        $ordersQuery->when($request->refund_status, fn($query) => $query->where('refund_status', $request->refund_status));
        $ordersQuery->when($request->branch_id, fn($query) => $query->where('branch_id', $request->branch_id));

        $ordersQuery->when($request->start_date && $request->end_date, function ($query) use ($request) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        });

        $ordersQuery->when($request->payment_status, function ($query) use ($request) {
            $query->where('payment_status', $request->payment_status);
        });

        $ordersQuery->when($request->search, fn($query) => $query->where('id', 'LIKE', '%' . $request->search . '%')
            ->orWhere('invoice_number', 'LIKE', '%' . $request->search . '%'));

        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        $orderStatusCounts = new AdminOrderStatusResource(Order::all());

        return response()->json([
            'orders' => AdminOrderResource::collection($orders),
            'meta' => new PaginationResource($orders),
            'status' => $orderStatusCounts,
        ]);
    }

    public function invoice(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::with([
            'customer',
            'orderDetail',
            'deliveryman',
            'orderAddress'
        ])
            ->where('id', $order_id)
            ->first();

        if (!$order) {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }
        return response()->json(new InvoiceResource($order));
    }

    public function changeOrderStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:' . implode(',', array_column(OrderStatusType::cases(), 'value')),
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::with(['customer','orderAddress'])->find($request->order_id);

        if (!$order) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        $userId = auth('api')->id();

        // Handle cancellation
        if ($request->status === 'cancelled') {
            $order->cancelled_by = $userId;
            $order->cancelled_at = Carbon::now();
            $order->status = 'cancelled';
            $success = $order->save();

            // Notification + Email
            $this->sendOrderDeliveredNotifications($order, null, 'admin_order_status_cancelled');

            return response()->json([
                'message' => __($success ? 'messages.update_success' : 'messages.update_failed', ['name' => 'Order status'])
            ], $success ? 200 : 500);
        }

        // Handle delivery
        if ($request->status === 'delivered') {
            $deliveryHistory = OrderDeliveryHistory::where('order_id', $order->id)
                ->where('status', 'accepted')
                ->whereNotIn('order_id', function ($query) {
                    $query->select('order_id')
                        ->from('order_delivery_histories')
                        ->where('status', 'cancelled');
                })->first();

            // Wallet updates
            $this->updateWallets($order, $deliveryHistory);

            OrderDeliveryHistory::create([
                'order_id' => $order->id,
                'deliveryman_id' => $deliveryHistory ? $deliveryHistory->deliveryman_id : $userId,
                'status' => 'delivered',
            ]);

            if ($order->payment_gateway === 'cash_on_delivery') {
                $order->update(['payment_status' => 'paid']);

                OrderActivity::create([
                    'order_id' => $order->id,
                    'activity_from' => 'admin',
                    'activity_type' => OrderActivityType::CASH_COLLECTION->value,
                    'ref_id' => $deliveryHistory?->deliveryman_id ?? $userId,
                    'activity_value' => $order->order_amount
                ]);
            }

            // Final update
            $order->delivery_completed_at = Carbon::now();
            $order->status = 'delivered';
            $success = $order->save();

            // Notification + Email
            $this->sendOrderDeliveredNotifications($order, $deliveryHistory, 'admin_order_status_delivery');

            return response()->json([
                'message' => __($success ? 'messages.update_success' : 'messages.update_failed', ['name' => 'Order status'])
            ], $success ? 200 : 500);
        }

        // Other status updates
        $order->status = $request->status;
        $success = $order->save();

        // if order status confirmed, processing, pickup, shipped
        $this->sendOrderDeliveredNotifications($order, null, 'admin_order_status_cpps');

        return response()->json([
            'message' => __($success ? 'messages.update_success' : 'messages.update_failed', ['name' => 'Order status'])
        ], $success ? 200 : 500);
    }

    protected function updateWallets(Order $order, $deliveryHistory)
    {

        // Deliveryman Wallet
        if ($deliveryHistory) {
            $deliverymanWallet = Wallet::where('owner_id', $deliveryHistory->deliveryman_id)
                ->where('owner_type', WalletOwnerType::DELIVERYMAN->value)
                ->first();

            if ($deliverymanWallet) {
                $deliverymanWallet->increment('balance', $order->delivery_charge_admin);
                $deliverymanWallet->increment('earnings', $order->delivery_charge_admin);

                WalletTransaction::create([
                    'wallet_id' => $deliverymanWallet->id,
                    'amount' => $order->delivery_charge_admin,
                    'type' => 'credit',
                    'purpose' => 'Delivery Earnings',
                    'status' => 1,
                ]);
            }
        }
    }

    protected function sendOrderDeliveredNotifications(Order $order, $deliveryHistory = null, $type = null)
    {

        // check if change admin order  status
        $this->orderManageNotificationService->createOrderNotification($order->id, $type);

        try {

            $emailTemplates = EmailTemplate::whereIn('type', [
                'order-status-delivered',
                'order-status-delivered-store',
                'order-status-delivered-admin',
                'deliveryman-earning'
            ])->where('status', 1)->get()->keyBy('type');

            $orderAmount = amount_with_symbol_format($order->order_amount);

            // Customer
            $customerMessage = str_replace(
                ["@customer_name", "@order_id", "@order_amount"],
                [$order->orderMaster?->customer?->full_name, $order->id, $orderAmount],
                $emailTemplates['order-status-delivered']?->body ?? ''
            );

            // Store
            $storeMessage = str_replace(
                ["@store_name", "@order_id", "@order_amount_for_store"],
                [$order->store?->name, $order->id, amount_with_symbol_format($order->order_amount_store_value)],
                $emailTemplates['order-status-delivered-store']?->body ?? ''
            );

            // Admin
            $adminMessage = str_replace(
                ["@order_id", "@order_amount_admin_commission", "@delivery_charge_commission_amount"],
                [$order->id, amount_with_symbol_format($order->order_amount_admin_commission), amount_with_symbol_format($order->delivery_charge_admin_commission)],
                $emailTemplates['order-status-delivered-admin']?->body ?? ''
            );

            // Deliveryman
            if ($deliveryHistory) {
                $deliverymanMessage = str_replace(
                    ["@name", "@order_id", "@order_amount", "@earnings_amount"],
                    [auth('api')->user()->full_name, $order->id, $orderAmount, amount_with_symbol_format($order->delivery_charge_admin)],
                    $emailTemplates['deliveryman-earning']?->body ?? ''
                );
            }

            // Sending
            Mail::to($order->orderAddress?->email ?? $order->orderMaster?->customer?->email)
                ->send(new DynamicEmail($emailTemplates['order-status-delivered']->subject ?? 'Order Delivered', $customerMessage));

            Mail::to($order->store?->email)->send(new DynamicEmail($emailTemplates['order-status-delivered-store']->subject ?? 'Order Delivered', $storeMessage));
            Mail::to(com_option_get('com_site_email'))->send(new DynamicEmail($emailTemplates['order-status-delivered-admin']->subject ?? 'Order Delivered', $adminMessage));

            if ($deliveryHistory) {
                Mail::to($order->deliveryman?->email)->send(new DynamicEmail($emailTemplates['deliveryman-earning']->subject ?? 'Delivery Earnings', $deliverymanMessage));
            }

        } catch (\Exception $e) {
            // Optional: log or ignore
        }
    }


    public function changePaymentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:pending,partially_paid,paid,cancelled,failed,refunded',
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

        $order->payment_status = $request->status;
        $success = $order->save();

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Order payment status'])
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Order payment status'])
            ], 500);
        }
    }

    public function assignDeliveryMan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
            'delivery_man_id' => 'required|exists:users,id',
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

        if ($order->status === 'processing') {
            $deliveryIsAccepted = OrderDeliveryHistory::where('order_id', $order->id)
                ->where('deliveryman_id', $request->delivery_man_id)
                ->where('status', 'accepted')
                ->exists();

            $deliveryIsCancelled = OrderDeliveryHistory::where('order_id', $order->id)
                ->where('deliveryman_id', $request->delivery_man_id)
                ->where('status', 'cancelled')
                ->exists();

            if ($deliveryIsAccepted && !$deliveryIsCancelled) {
                return response()->json([
                    'message' => __('messages.deliveryman_order_already_taken')
                ]);
            }

            $success = $order->update([
                'confirmed_by' => $request->delivery_man_id
            ]);

            // Notification + Email
            $this->sendOrderDeliveredNotifications($order, null, 'admin_order_assign_deliveryman');

            if ($success) {
                return response()->json([
                    'message' => __('messages.deliveryman_assign_successful')
                ], 200);
            } else {
                return response()->json([
                    'message' => __('messages.deliveryman_assign_failed')
                ], 500);
            }

        } else {
            return response()->json([
                'message' => __('messages.deliveryman_can_not_be_assigned')
            ], 422);
        }
    }

    public function cancelOrder(Request $request)
    {
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
        if ($order->status !== 'delivered') {

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

                return response()->json([
                    'message' => __('messages.order_cancel_successful')
                ], 200);

            } else {
                return response()->json([
                    'message' => __('messages.order_cancel_failed')
                ], 500);
            }
        } else {
            return response()->json([
                'message' => __('messages.order_status_not_changeable')
            ], 422);
        }
    }

}
