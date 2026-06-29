<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\app\Models\Order;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Services\BkashService;
use Modules\Wallet\app\Models\WalletTransaction;

class BkashController extends Controller
{
    public function __construct(private BkashService $bkashService)
    {
        $this->middleware([
            'auth:api_customer',
            'check.customer.account.status',
        ])->except([
            'callback',
        ]);
    }

    public function createPayment(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'purpose' => 'required|in:order,wallet',
            'wallet_transaction_id' => 'required_if:purpose,wallet|exists:wallet_transactions,id',
            'order_id' => 'required_if:purpose,order|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->purpose === 'wallet'){
            $walletTransaction = WalletTransaction::with('wallet.owner')
                ->where('id', $request->wallet_transaction_id)
                ->firstOrFail();

            if ($walletTransaction->payment_status === 'paid') {
                return response()->json(['message' => 'Wallet already paid.'], 422);
            }

            // get payment gateway settings
            $bkash = PaymentGateway::where('slug', 'bkash')->firstOrFail();
            $credentials = $bkash->auth_credentials ? json_decode($bkash->auth_credentials, true) : null;
            $merchantInvoiceNumber = 'WAL-' . $walletTransaction->id . '-' . Str::upper(Str::random(6));
            $customer_number = (string)$walletTransaction->wallet?->owner?->phone;

            // currency conversion if needed
            if ($walletTransaction->currency_code && $walletTransaction->currency_code !== 'BDT') {
                $exchangeRate = $walletTransaction->exchange_rate ?? 1; // default to 1 if not set
                $walletTransaction->amount = round($walletTransaction->amount * $exchangeRate, 2);
            }

            $payableAmount = convertToBdt($walletTransaction->amount,$walletTransaction->currency_code);
            $amount = number_format((float) $payableAmount, 2, '.', '');


            $response = $this->bkashService->createPayment(
                [
                    'mode' => '0011',
                    'payerReference' => $customer_number,
                    'callbackURL' => $credentials['bkash_callback_url'] ?? null,
                    'amount' => (string) $amount,
                    'currency' => 'BDT',
                    'intent' => 'sale',
                    'merchantInvoiceNumber' => $merchantInvoiceNumber,
                ],
                $credentials
            );

            if (!empty($response['paymentID'])) {
                $walletTransaction->update([
                    'transaction_ref' => $response['paymentID'],
                    'invoice_number' => $merchantInvoiceNumber,
                    'payment_gateway' => 'bkash',
                    'payment_status' => 'pending',
                ]);
            }

        }else{
            $order = Order::with('orderAddress')
                ->where('id', $request->order_id)
                ->where('customer_id', auth('api_customer')->id())
                ->firstOrFail();

            if ($order->payment_status === 'paid') {
                return response()->json(['message' => 'Order already paid.'], 422);
            }

            // get payment gateway settings
            $bkash = PaymentGateway::where('slug', 'bkash')->firstOrFail();
            $credentials = $bkash->auth_credentials ? json_decode($bkash->auth_credentials, true) : null;
            $merchantInvoiceNumber = 'ORD-' . $order->id . '-' . Str::upper(Str::random(6));
            $customer_number = (string)$order->orderAddress?->contact_number;

            // currency conversion if needed
            if ($order->currency_code && $order->currency_code !== 'BDT') {
                $exchangeRate = $order->exchange_rate ?? 1; // default to 1 if not set
                $order->order_amount = round($order->order_amount * $exchangeRate, 2);
            }

            $payableAmount = convertToBdt($order->order_amount,$order->currency_code);
            $amount = number_format((float) $payableAmount, 2, '.', '');


            $response = $this->bkashService->createPayment(
                [
                    'mode' => '0011',
                    'payerReference' => $customer_number,
                    'callbackURL' => $credentials['bkash_callback_url'] ?? null,
                    'amount' => (string) $amount,
                    'currency' => 'BDT',
                    'intent' => 'sale',
                    'merchantInvoiceNumber' => $merchantInvoiceNumber,
                ],
                $credentials
            );

            if (!empty($response['paymentID'])) {
                $order->update([
                    'transaction_ref' => $response['paymentID'],
                    'invoice_number' => $merchantInvoiceNumber,
                    'payment_gateway' => 'bkash',
                    'payment_status' => 'pending',
                ]);
            }
        }



        return response()->json([
            'payment_id' => $response['paymentID'] ?? null,
            'bkash_url' => $response['bkashURL'] ?? null,
            'raw' => $response,
        ]);
    }

    public function callback(Request $request)
    {
        $paymentId = $request->payment_id;
        $status = $request->status ?? null;

        $validator = Validator::make($request->all(), [
            'purpose' => 'required|string|max:255|in:order,wallet',
            'status' => 'required|string|max:255',
            'payment_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if ($request->purpose == 'wallet'){
            $data = WalletTransaction::where('transaction_ref', $paymentId)->first();
            $message = 'Wallet Transaction not found';

            $main_id = [
                'wallet_transaction_id' => $data->id,
            ];

        }else{
            $data = Order::where('transaction_ref', $paymentId)->first();
            $message = 'Order not found';

            $main_id = [
                'order_id' => $data->id,
            ];
        }


        if (!$data) {
            return response()->json(['message' => $message], 404);
        }

        if ($data->payment_status === 'paid') {
            return response()->json([
                'status' => true,
                'message' => 'Payment already processed',
                'data' => array_merge($main_id,[
                    'payment_id' => $paymentId,
                    'trx_id' => $data->transaction_id ?? null,
                ])
            ], 200);
        }


        if ($status === 'success' || $status === 'Successful') {
            // get payment gateway settings
            $bkash = PaymentGateway::where('slug', 'bkash')->firstOrFail();
            $credentials = $bkash->auth_credentials ? json_decode($bkash->auth_credentials, true) : null;

            $execute = $this->bkashService->executePayment($paymentId, $credentials);

            if (($execute['transactionStatus'] ?? null) === 'Completed' && ($execute['statusCode'] ?? null) === '0000') {

                // update order payment status and transaction reference
                $data->update([
                    'payment_status' => 'paid',
                    'transaction_ref' => $execute['trxID'] ?? null,
                    'created_at' => now(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'payment  paid successfully',
                    'data' => array_merge($main_id,[
                        'payment_id' => $paymentId,
                        'trx_id' => $execute['trxID'] ?? null,
                        'amount' => $execute['amount'] ?? null,
                        'currency' => $execute['currency'] ?? null,
                        'raw' => $execute,
                    ])
                ], 200);

            } else {
                $data->update(['payment_status' => 'failed']);

                return response()->json([
                    'status' => false,
                    'message' => 'payment  failed',
                    'data' => array_merge($main_id,[
                        'payment_id' => $paymentId,
                        'trx_id' => $execute['trxID'] ?? null,
                        'amount' => $execute['amount'] ?? null,
                        'currency' => $execute['currency'] ?? null,
                        'raw' => $execute,
                    ])
                ], 200);
            }
        } elseif ($status === 'cancel') {
            $data->update(['payment_status' => 'cancelled']);

            return response()->json([
                'status' => false,
                'message' => 'payment  cancelled',
            ], 200);
        } elseif ($status === 'failure') {
            $data->update(['payment_status' => 'failed']);

            return response()->json([
                'status' => false,
                'message' => 'payment  failed',
            ], 200);
        }else {
                return response()->json([
                    'status' => false,
                    'message' => 'payment  pending',
                ], 200);
            }

    }




    public function paymentStatus(Request $request)
    {
        $paymentId = $request->payment_id;

        if (!$paymentId) {
            return response()->json([
                'status' => false,
                'message' => 'paymentID is required',
            ], 422);
        }


          $order = Order::where('transaction_ref', $paymentId)->first();

            if (!$order) {
                return response()->json([
                  'message' => 'Order not found'
                ], 404);
            }

            // get payment gateway settings
            $bkash = PaymentGateway::where('slug', 'bkash')->firstOrFail();
            $credentials = $bkash->auth_credentials ? json_decode($bkash->auth_credentials, true) : null;

          $response = $this->bkashService->queryPayment($paymentId, $credentials);

        if (!$response) {
            return response()->json([
                'status' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $transactionStatus = $response['transactionStatus'] ?? null;

        return response()->json([
            'status' => true,
            'message' => 'Payment status fetched successfully',
            'data' => [
                'order_id' => $order->id,
                'payment_id' => $paymentId,
                'trx_id' => $response['trxID'] ?? null,
                'transaction_status' => $transactionStatus,
                'payment_status' => $order->payment_status,
                'amount' => $response['amount'] ?? null,
                'currency' => $response['currency'] ?? null,
                'raw' => $response,
            ]
        ]);

    }

}
