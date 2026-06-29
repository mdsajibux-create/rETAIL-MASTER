<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\app\Models\Order;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Services\SSLCommerzService;
use Modules\Wallet\app\Models\WalletTransaction;


class SslcommerzController extends Controller
{
    public function __construct(private SSLCommerzService $ssl) {}

    public function initiate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purpose' => 'required|in:order,wallet',
            'wallet_transaction_id' => 'required_if:purpose,wallet|exists:wallet_transactions,id',
            'order_id' => 'required_if:purpose,order|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // payment gateway settings from database
        $gateway     = PaymentGateway::where('slug', 'sslcommerz')->firstOrFail();
        $credentials = $gateway->auth_credentials ? json_decode($gateway->auth_credentials, true) : [];

        // Depending on the purpose, we either process an order payment or a wallet top-up
        if ($request->purpose === 'wallet') {

            // find the order and ensure it belongs to the authenticated customer
            $wallet_transaction = WalletTransaction::with('wallet.owner')
                ->whereHas('wallet', function ($query) {
                    $query->where('owner_id', auth('api_customer')->id());
                })
                ->findOrFail((int)$request->wallet_transaction_id);

            // ensure the order belongs to the authenticated customer
            if ($wallet_transaction->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Wallet Transaction already paid.'
                ],409);
            }

            $tranId = 'WLT-' . strtoupper(Str::random(12));
            $user = $wallet_transaction->wallet->owner;

            // set callback urls
            $callback_url = rtrim(
                $credentials['sslcommerz_callback_url'] ?? '',
                '/'
            );
            // modified callback url
            $callback_final_url = $callback_url . '/wallet';

            // currency conversion if needed
            $payableAmount = convertToBdt($wallet_transaction->amount,$wallet_transaction->currency_code);


            // data for SSLCommerz
            $postData = [
                'total_amount'     => $payableAmount,
                'currency'         => 'BDT',
                'tran_id'          => $tranId,
                'success_url'      => route('payment.success'),
                'fail_url'         => route('payment.fail'),
                'cancel_url'       => route('payment.cancel'),
                'ipn_url'          => route('payment.ipn'),
                'cus_name'         => $user->full_name ?? 'Customer',
                'cus_email'        => $user->email ?? 'noreply@example.com',
                'cus_phone'        => $user->phone        ?? '01700000000',
                'cus_add1'         => $user->address      ?? 'N/A',
                'cus_city'         => $user->city         ?? 'N/A',
                'cus_country'      => 'Bangladesh',
                'product_name'     => 'Wallet Deposit #' . $wallet_transaction->id,
                'product_category' => 'wallet_deposit',
                'product_profile'  => 'non-physical-goods', // Crucial for digital wallet top-ups
                'shipping_method'  => 'NO',
                'ship_name'        =>  $user->full_name ?? 'Customer',
                'ship_add1'        =>  'N/A',
                'ship_city'        =>  'N/A',
                'ship_country'     => 'Bangladesh',
            ];

            try {
                // update order with transaction reference and gateway before initiating payment to ensure we can track it in callbacks
                $wallet_transaction->update([
                    'transaction_ref' => $tranId,
                    'payment_gateway' => 'sslcommerz',
                ]);

                $response = $this->ssl->initiatePayment($postData);

                return response()->json([
                    'gateway_url' => $response['GatewayPageURL'],
                    'tran_id'     => $tranId,
                ]);
            } catch (\Exception $e) {

                return response()->json(['message' => $e->getMessage()], 500);
            }

        }else{
            // find the order and ensure it belongs to the authenticated customer
            $order = Order::with('orderAddress')
                ->where('customer_id', auth('api_customer')->id())
                ->findOrFail((int)$request->order_id);

            // ensure the order belongs to the authenticated customer
            if ($order->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Order already paid.'
                ],409);
            }

            $tranId = 'ORD-' . strtoupper(Str::random(12));


            // currency conversion if needed
            $payableAmount = convertToBdt($order->order_amount,$order->currency_code);

            // data for SSLCommerz
            $postData = [
                'total_amount'     => $payableAmount,
                'currency'         => 'BDT',
                'tran_id'          => $tranId,
                'success_url'      => route('payment.success'),
                'fail_url'         => route('payment.fail'),
                'cancel_url'       => route('payment.cancel'),
                'ipn_url'          => route('payment.ipn'),
                'cus_name'         => $order->orderAddress?->name         ?? 'Customer',
                'cus_email'        => $order->orderAddress?->email        ?? 'noreply@example.com',
                'cus_phone'        => $order->orderAddress?->phone        ?? '01700000000',
                'cus_add1'         => $order->orderAddress?->address      ?? 'N/A',
                'cus_city'         => $order->orderAddress?->city         ?? 'Dhaka',
                'cus_country'      => 'Bangladesh',
                'product_name'     => 'Order #' . $order->id,
                'product_category' => 'general',
                'product_profile'  => 'general',
                'shipping_method'  => 'NO',
                'ship_name'        => $order->orderAddress?->name         ?? 'Customer',
                'ship_add1'        => $order->orderAddress?->address      ?? 'N/A',
                'ship_city'        => $order->orderAddress?->city         ?? 'Dhaka',
                'ship_country'     => 'Bangladesh',
            ];

            try {
                // update order with transaction reference and gateway before initiating payment to ensure we can track it in callbacks
                $order->update([
                    'transaction_ref' => $tranId,
                    'payment_gateway' => 'sslcommerz',
                ]);

                $response = $this->ssl->initiatePayment($postData);

                return response()->json([
                    'gateway_url' => $response['GatewayPageURL'],
                    'tran_id'     => $tranId,
                ]);
            } catch (\Exception $e) {

                return response()->json(['message' => $e->getMessage()], 500);
            }
        }


    }

    public function ipn(Request $request)
    {
        if (! $this->ssl->verifyIpnHash($request->all())) {
            return response()->json(['message' => 'Invalid IPN.'], 400);
        }

        $this->handleCallback($request->all(), 'IPN');

        return response()->json(['message' => 'IPN received.']);
    }

    public function paymentSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
            'tran_id' => 'required|string|max:255',
            'val_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $this->handleCallback($request->all(), 'SUCCESS');

        // payment gateway settings from database
        $gateway     = PaymentGateway::where('slug', 'sslcommerz')->firstOrFail();
        $credentials = $gateway->auth_credentials ? json_decode($gateway->auth_credentials, true) : [];
        $sslcommerz_callback_url = $credentials['sslcommerz_callback_url'] ?? '';

        $tranId = $request->tran_id;
        if (str_starts_with($tranId, 'WLT-')) {
            $WalletTransaction = WalletTransaction::where('transaction_ref', $tranId)->first();

            if (! $WalletTransaction) {
               return redirect()->away($sslcommerz_callback_url . '/payment/failed');
            }

            return redirect()->away($sslcommerz_callback_url . '/customer/wallet');

        }else{
            $order = Order::where('transaction_ref', $tranId)->first();

            if (! $order) {
                return redirect()->away($sslcommerz_callback_url . '/payment/failed');
            }

            return redirect()->away($sslcommerz_callback_url . '/order-confrimed');
        }

    }

    public function paymentFail(Request $request)
    {

        $tranId = $request->tran_id;

        if (str_starts_with($tranId, 'WLT-')) {
            $WalletTransaction = WalletTransaction::where('transaction_ref', $tranId)->first();

            if (! $WalletTransaction) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            if ($WalletTransaction->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Transaction already paid.'
                ], 409);
            }

            // update order payment status to failed
            $WalletTransaction->update([
                'payment_status'      => 'failed',
                'transaction_details' => $request->all(),
            ]);

            return response()->json([
                'status'  => 'failed',
                'tran_id' => $tranId,
                'message' => 'Payment failed.',
            ]);

        }else{

            $order = Order::where('id', (int) $request->order_id)
                ->where('transaction_ref', $request->tran_id)
                ->first();

            if ($order->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Order already paid.'
                ], 409);
            }

            // update order payment status to failed
            $order->update([
                'payment_status'      => 'failed',
                'transaction_details' => $request->all(),
            ]);

            return response()->json([
                'status'  => 'failed',
                'tran_id' => $tranId,
                'message' => 'Payment failed.',
            ]);
        }


    }

    public function paymentCancel(Request $request)
    {

        $tranId = $request->tran_id;

        if (str_starts_with($tranId, 'WLT-')) {
            $WalletTransaction = WalletTransaction::where('transaction_ref', $tranId)->first();

            if (! $WalletTransaction) {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            if ($WalletTransaction->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Order already paid.'
                ], 409);
            }

            // update order payment status to cancelled
            $WalletTransaction->update([
                'payment_status'      => 'cancelled',
                'transaction_details' => $request->all(),
            ]);

            return response()->json([
                'status'  => 'cancelled',
                'tran_id' => $tranId,
                'message' => 'Payment cancelled.',
            ]);

        }else{

            $order = Order::where('id', (int) $request->order_id)
                ->where('transaction_ref', $request->tran_id)
                ->first();

            if ($order->payment_status === 'paid') {
                return response()->json([
                    'message' => 'Order already paid.'
                ], 409);
            }

            // update order payment status to cancelled
            $order->update([
                'payment_status'      => 'cancelled',
                'transaction_details' => $request->all(),
            ]);

            return response()->json([
                'status'  => 'cancelled',
                'tran_id' => $tranId,
                'message' => 'Payment cancelled.',
            ]);
        }


    }

    public function status(string $tranId)
    {

        if (str_starts_with($tranId, 'WLT-')) {
            $WalletTransaction = WalletTransaction::where('transaction_ref', $tranId)->first();

            if (! $WalletTransaction) {
                return response()->json(['message' => 'Transaction not found.'], 404);
            }

            return response()->json([
                'from'  => 'wallet',
                'tran_id'  => $tranId,
                'wallet_transaction_id' => $WalletTransaction->id,
                'status' => $WalletTransaction->status,
                'payment_status' => $WalletTransaction->payment_status,
                'payment_gateway' => $WalletTransaction->payment_gateway,
                'amount' => $WalletTransaction->amount,
            ]);
        }else{

            $order = Order::where('customer_id', auth('api_customer')->id())
                ->where('transaction_ref', $tranId)
                ->first();

            if (! $order) {
                return response()->json(['message' => 'Order not found.'], 404);
            }

            return response()->json([
                'from'  => 'order',
                'order_id' => $order->id,
                'status' => $order->status,
                'tran_id'  => $order->transaction_ref,
                'payment_status'   => $order->payment_status,
                'payment_gateway'   => $order->payment_gateway,
                'amount'   => $order->order_amount,
            ]);
        }


    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private function handleCallback(array $data, string $source): void
    {
        $tranId = $data['tran_id'] ?? null;
        if (! $tranId) return;

        // wallet execution explicitly separated pipelines
        if (str_starts_with($tranId, 'WLT-')) {

            $wallet_transaction = WalletTransaction::with('wallet')
                ->where('transaction_ref', $tranId)
                ->first();

            // check payment status
            if (! $wallet_transaction || in_array($wallet_transaction->payment_status, [
                    'paid',
                    'failed',
                    'cancelled'
                ])) return;


            try {
                $valId      = $data['val_id'] ?? null;
                $validation = $valId ? $this->ssl->validatePayment($valId) : null;

                // check status
                $isValid = in_array(($validation['status'] ?? ''), ['VALID', 'VALIDATED']) && (float) ($validation['amount'] ?? 0) >= (float) $wallet_transaction->amount;

                // if validation fails, we still want to log the callback data for troubleshooting, but mark the payment as failed
                if ($isValid) {
                    $wallet_transaction->update([
                        'payment_status'   => 'paid',
                        'status'   => 1,
                        'val_id'           => $valId,
                        'transaction_details' => json_encode($data),
                    ]);

                    // update wallet balance
                    $wallet = $wallet_transaction->wallet;
                    $wallet->balance += $wallet_transaction->amount;
                    $wallet->save();

                } else {
                    $wallet_transaction->update([
                        'payment_status'      => 'failed',
                        'val_id'           => $valId,
                        'transaction_details' => json_encode($data),
                    ]);
                }
            } catch (\Exception $e) {
            }

        } elseif (str_starts_with($tranId, 'ORD-')) {
            $order = Order::where('transaction_ref', $tranId)->first();

            // check payment status
            if (! $order || in_array($order->payment_status, [
                    'paid',
                    'failed',
                    'cancelled'
                ])) return;


            try {
                $valId      = $data['val_id'] ?? null;
                $validation = $valId ? $this->ssl->validatePayment($valId) : null;

                // check status
                $isValid = in_array(($validation['status'] ?? ''), ['VALID', 'VALIDATED']) && (float) ($validation['amount'] ?? 0) >= (float) $order->order_amount;


                // if validation fails, we still want to log the callback data for troubleshooting, but mark the payment as failed
                if ($isValid) {
                    $order->update([
                        'payment_status'   => 'paid',
                        'val_id'           => $valId,
                        'transaction_details' => json_encode($data),
                    ]);
                } else {
                    $order->update([
                        'payment_status'      => 'failed',
                        'val_id'           => $valId,
                        'transaction_details' => json_encode($data),
                    ]);
                }
            } catch (\Exception $e) {
            }
        }


    }


}
