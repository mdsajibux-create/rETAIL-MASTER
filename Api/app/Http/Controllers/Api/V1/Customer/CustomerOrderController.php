<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Helpers\ComHelper;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\OrderPaymentTrackingResource;
use App\Http\Resources\Com\OrderRefundTrackingResource;
use App\Http\Resources\Com\OrderTrackingResource;
use App\Http\Resources\Com\PaginationResource;
use App\Services\Order\OrderManageNotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Coupon\app\Models\CouponLine;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderDetail;
use Modules\Order\app\Transformers\CustomerOrderResource;
use Modules\Order\app\Transformers\InvoiceResource;
use Modules\Order\app\Transformers\OrderRefundRequestResource;
use Modules\Order\app\Transformers\OrderSummaryResource;
use Modules\Product\app\Models\FlashSale;
use Modules\Product\app\Models\ProductStock;

class CustomerOrderController extends Controller
{

    protected OrderManageNotificationService $orderManageNotificationService;

    public function __construct(OrderManageNotificationService $orderManageNotificationService)
    {
        $this->orderManageNotificationService = $orderManageNotificationService;
    }

    public function myOrders(Request $request)
    {
        $customer_id = auth()->guard('api_customer')->user()->id;
        $order_id = $request->order_id;

        $ordersQuery = Order::with([
            'customer',
            'orderDetail.product',
            'deliveryman',
            'orderAddress'
        ])
            ->where('customer_id', $customer_id)
            ->whereNot('order_type', 'pos');


        if ($order_id) {
            $order = $ordersQuery->where('id', $order_id)->first();
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            return response()->json([
                'order_data' => new CustomerOrderResource($order),
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


        $request['status'] = $request->status == 'active' ? 'confirmed' : $request->status;
        $ordersQuery->when($request->status, fn($query) => $query->where('status', $request->status));

        if (!empty($request->payment_status)){
            $ordersQuery->where('payment_status', $request->payment_status);
        }

        $ordersQuery->when($request->search, fn($query) => $query
            ->where('id', 'like', '%' . $request->search . '%')
            ->orwhere('invoice_number', 'like', '%' . $request->search . '%'));

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate($request->per_page ?? 10);

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => CustomerOrderResource::collection($orders),
            'meta' => new PaginationResource($orders)
        ]);
    }

    public function orderInvoice(Request $request)
    {
        $order_id = $request->order_id;
        $customer_id = auth()->guard('api_customer')->user()->id;

        $order = Order::with([
            'customer',
            'orderDetail',
            'deliveryman',
            'orderAddress'
        ])
            ->where('id', $order_id)
            ->where('customer_id', $customer_id)
            ->first();

        if (!$order) {
            return response()->json(['message' => __('messages.data_not_found')], 404);
        }
        return response()->json(new InvoiceResource($order));
    }

    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer_id = auth()->guard('api_customer')->user()->id;
        $order = Order::where('id', $request->order_id)->first();

        if (!$order) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        // check right customer order
        if ($order->customer_id != $customer_id) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        if ($order->status === 'cancelled') {
            return response()->json([
                'message' => __('messages.order_already_cancelled')
            ], 422);
        }

        if ($order->status === 'delivered') {
            return response()->json([
                'message' => __('messages.order_already_delivered')
            ], 422);
        }

        $order->cancelled_by = auth('api_customer')->user()->id;
        $order->cancelled_at = Carbon::now();
        $order->status = 'cancelled';
        $success = $order->save();

        // notification send
        $this->orderManageNotificationService->createOrderNotification($order->id, 'customer_order_status_cancelled');

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

    }

    public function checkCoupon(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'coupon_code' => 'required|string|exists:coupon_lines,coupon_code',
            'currency_code' => 'required|string|exists:currencies,code',
            'sub_total' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        $coupon = CouponLine::where('coupon_code', $request->coupon_code)->first();

        if (!$coupon) {
            return response()->json([
                'message' => __('messages.coupon_not_found'),
            ], 404);
        }

        if ($coupon->customer_id !== null) {
            if (!auth('api_customer')->check()) {
                unauthorized_response();
            }

            if ($coupon->customer_id && $coupon->customer_id !== auth('api_customer')->id()) {
                return response()->json([
                    'message' => __('messages.coupon_does_not_belong'),
                ], 422);
            }
        }

        // Check if the coupon is active based on the start and end dates
        if ($coupon->start_date && $coupon->start_date > now()) {
            return response()->json([
                'status' => false,
                'message' => __('messages.coupon_inactive'),
            ], 422);
        }

        if ($coupon->end_date && $coupon->end_date < now()) {
            return response()->json([
                'message' => __('messages.coupon_expired'),
            ], 422);
        }

        // Check if the coupon usage limit has been reached
        if ($coupon->usage_limit == 0) {
            return response()->json([
                'message' => __('messages.coupon_limit_reached'),
            ], 422);
        }
        if ($coupon->coupon->status != 1 && $coupon->status != 1) {
            return response()->json([
                'message' => __('messages.coupon_inactive'),
            ], 422);
        }


        $currencyData = ComHelper::getCurrencyInfo($request->currency_code);

        // subtotal → system currency
        $subTotalInSystemCurrency = floor(($request->sub_total / $currencyData['exchange_rate']) * 100) / 100;

        // check min_order status
        if ($subTotalInSystemCurrency < $coupon->min_order_value) {
            // convert system min_order_value → user currency for message
            $minOrderInUserCurrency = round($coupon->min_order_value * $currencyData['exchange_rate'], 2);

            return response()->json([
                'message' => __('messages.coupon_min_order_amount', [
                    'amount' => amount_with_symbol_format_for_response($minOrderInUserCurrency,$request->currency_code ?? $currencyData['currency_code']),
                    ]),
            ], 422);
        }

        $sub_total = $request->sub_total;
        $final_amount_after_removing_coupon_discount = 0;
        $discount_amount = 0;

        if ($coupon->discount_type == 'percentage') {
            $discount_amount = $sub_total / 100 * $coupon->discount;
            $final_amount_after_removing_coupon_discount = $sub_total - $discount_amount;
        } elseif ($coupon->discount_type == 'amount') {
            $discount_amount = $coupon->discount;
            $final_amount_after_removing_coupon_discount = $sub_total - $discount_amount;
        } else {
            return response()->json([
                'message' => __('messages.something_wrong'),
            ], 500);
        }

        // check max discount amount
        if ($discount_amount > $coupon->max_discount) {
            $discount_amount = $coupon->max_discount;
            $final_amount_after_removing_coupon_discount = $sub_total - $discount_amount;
        }

        return response()->json([
            'message' => __('messages.coupon_applied'),
            'coupon' => [
                'title' => $coupon->coupon->title,
                'discount_amount' => $coupon->discount,
                'max_discount' => $coupon->max_discount,
                'min_order_value' => $coupon->min_order_value,
                'discount_type' => $coupon->discount_type,
                'code' => $coupon->coupon_code,
                'discounted_amount' => $discount_amount,
                'final_amount' => $final_amount_after_removing_coupon_discount,
            ]
        ]);
    }
}
