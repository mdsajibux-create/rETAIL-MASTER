<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\app\Models\Order;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Services\NagadService;

class NagadController extends Controller
{
    public function __construct(private NagadService $nagadService)
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
            'order_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::with('orderAddress')
            ->where('id', $request->order_id)
            ->where('customer_id', auth('api_customer')->id())
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return response()->json(['message' => 'Order already paid.'], 422);
        }

        // get payment gateway settings
        $nagad = PaymentGateway::where('slug', 'nagad')->firstOrFail();
        $credentials = $nagad->auth_credentials ? json_decode($nagad->auth_credentials, true) : null;
        $credentials['is_test_mode'] =  $nagad->is_test_mode;

        $merchantInvoiceNumber = 'ORD-' . $order->id . '-' . Str::upper(Str::random(6));

        $payerReference = preg_replace(
            '/\D+/',
            '',
            $order->orderAddress?->contact_number ?? $order->customer_phone ?? ''
        );

        if (strlen($payerReference) < 11) {
            return response()->json([
                'message' => 'Valid payer reference is required.',
            ], 422);
        }

        $response = $this->nagadService->createPayment(
           [
               'payerReference' => '01799328264' ?? $payerReference,
               'callbackURL' => $credentials['nagad_callback_url'] ?? null,
               'order_id' => $order->id,
               'amount' => '2' ?? number_format((float) $order->payable_amount, 2, '.', ''),
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
                'payment_gateway' => 'nagad',
                'payment_status' => 'pending',
            ]);
        }

        return response()->json([
            'payment_id' => $response['paymentID'] ?? null,
            'nagad_url' => $response['nagadURL'] ?? $response['paymentURL'] ?? null,
            'raw' => $response,
        ]);
    }

    public function callback(Request $request)
    {
        $paymentId = $request->payment_id ?? $request->payment_id;
        $status = $request->status ?? null;

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:255',
            'payment_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $order = Order::where('transaction_ref', $paymentId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        if ($status === 'success' || $status === 'Successful') {

            // get payment gateway settings
            $bkash = PaymentGateway::where('slug', 'bkash')->firstOrFail();
            $credentials = $bkash->auth_credentials ? json_decode($bkash->auth_credentials, true) : null;

            $execute = $this->nagadService->executePayment($paymentId, $credentials);

            if (($execute['transactionStatus'] ?? null) === 'Completed') {
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_ref' => $execute['trxID'] ?? null,
                    'created_at' => now(),
                ]);

                return response()->json([
                    'status' => true,
                    'message' => 'payment  paid successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'payment_id' => $paymentId,
                        'trx_id' => $execute['trxID'] ?? null,
                        'amount' => $execute['amount'] ?? null,
                        'currency' => $execute['currency'] ?? null,
                        'raw' => $execute,
                    ]
                ], 200);

            } else {
                $order->update(['payment_status' => 'failed']);

                return response()->json([
                    'status' => false,
                    'message' => 'payment  failed',
                    'data' => [
                        'order_id' => $order->id,
                        'payment_id' => $paymentId,
                        'trx_id' => $execute['trxID'] ?? null,
                        'amount' => $execute['amount'] ?? null,
                        'currency' => $execute['currency'] ?? null,
                        'raw' => $execute,
                    ]
                ], 200);
            }
        } elseif ($status === 'cancel') {
            $order->update(['payment_status' => 'cancelled']);

            return response()->json([
                'status' => false,
                'message' => 'payment  cancelled',
            ], 200);
        } elseif ($status === 'failure') {
            $order->update(['payment_status' => 'failed']);

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
        $paymentId = $request->payment_id ?? $request->payment_id;

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
            $nagad = PaymentGateway::where('slug', 'nagad')->firstOrFail();
            $credentials = $nagad->auth_credentials ? json_decode($nagad->auth_credentials, true) : null;

          $response = $this->nagadService->queryPayment($paymentId, $credentials);

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
