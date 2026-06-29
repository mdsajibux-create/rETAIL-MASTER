<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\Order\PlaceOrderRequest;
use App\Models\Customer;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Transformers\PlaceOrderDetailsResource;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductVariant;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;

class PlaceOrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {

        $data = $request->validated();
        $user = auth()->guard('api_customer')->user();

        // login check
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to proceed with the order.',
            ], 400);
        }

        $order = $this->orderService->createOrder($data);

        foreach ($data['items'] as $item) {
            $this->updateProductData($item['product_id']);
            $this->updateVariantData($item['variant_id'], $item['quantity']);
        }

        // if return false
        if ($order === false || empty($order)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order. Please try again.',
            ], 400);
        }else{
            $order = $order[0];
        }

        try {
            if (!empty($order)) {
                if ($order->payment_gateway === 'wallet') {

                    $success = $this->updateWallet($order->order_amount);
                    $order_id = $order->id;

                    if (!empty($success)) {
                        Order::where('id', $order_id)->update([
                            'payment_gateway' => 'wallet',
                            'payment_status' => 'paid',
                        ]);
                    } else {
                        Order::where('id', $order_id)->update([
                            'payment_gateway' => 'wallet',
                            'payment_status' => 'pending',
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Order placed successfully.',
                    'orders' => new  PlaceOrderDetailsResource($order),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while placing the order.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while placing the order.',
            ], 500);
        }
    }


    private function updateProductData(int $productId): bool
    {
        return Product::where('id', $productId)->increment('order_count') > 0;
    }

    private function updateVariantData(int $variantId, int $quantity): bool
    {
        $variant = ProductVariant::find($variantId);

        if (!$variant) {
            return false;
        }

        $variant->increment('order_count');
        if ($variant->stock_quantity >= $quantity) {
            $variant->decrement('stock_quantity', $quantity);
        } else {
            // Optional: handle out-of-stock or insufficient quantity case
            return false;
        }

        return true;
    }

    private function updateWallet(int $order_amount)
    {
        $customer = auth()->guard('api_customer')->user();

        if (!$customer) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        $wallet = Wallet::where('owner_id', $customer->id)
            ->where('owner_type', Customer::class)
            ->first();

        if (!$wallet || $wallet->balance <= 0 || $wallet->balance < $order_amount) {
            return response()->json([
                'message' => __('messages.insufficient_balance')
            ], 422);
        }

        $wallet->balance -= $order_amount;
        $wallet->save();

        // create wallet history
        WalletTransaction::create([
            'wallet_id'            => $wallet->id,
            'transaction_ref'      => 'ORDER-' . time(),
            'transaction_details'  => 'Order payment from wallet',
            'amount'               => $order_amount,
            'type'                 => 'debit', // debit = money out
            'purpose'              => 'order_payment',
            'payment_gateway'      => 'wallet',
            'payment_status'       => 'paid',
            'status'               => 1,
            'currency_code'        => config('app.currency_code', 'USD'),
            'exchange_rate'        => 1,
        ]);


        return $wallet;
    }
}
