<?php

namespace Modules\PaymentGateways\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\app\Models\Order;
use Modules\PaymentGateways\app\Models\PaymentGateway;
use Modules\PaymentGateways\app\Services\NagadServiceTwo;
use Exception;

class NagadTwoController extends Controller
{
    public function __construct(private NagadServiceTwo $nagadService)
    {
        $this->middleware([
            'auth:api_customer',
            'check.customer.account.status',
        ])->except(['callback']);
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

        $nagad = PaymentGateway::where('slug', 'nagad')->firstOrFail();
        $credentials = $nagad->auth_credentials ? json_decode($nagad->auth_credentials, true) : [];
        $credentials['is_test_mode'] = (bool) $nagad->is_test_mode;

        $merchantInvoiceNumber = 'ORD-' . $order->id . '-' . Str::upper(Str::random(6));
        $payerReference = preg_replace('/\D+/', '', $order->orderAddress?->contact_number ?? $order->customer_phone ?? '');

        if (strlen($payerReference) < 11) {
            return response()->json(['message' => 'Valid payer reference is required.'], 422);
        }

        try {
            $response = $this->nagadService->createPayment([
                'payerReference'        => $payerReference,
                'callbackURL'           => $credentials['nagad_callback_url'] ?? null,
                'order_id'              => $order->id,
                'amount'                => number_format((float) $order->payable_amount, 2, '.', ''),
                'currency'              => 'BDT',
                'intent'                => 'sale',
                'merchantInvoiceNumber' => $merchantInvoiceNumber,
            ], $credentials);

            if (!empty($response['paymentID'])) {
                $order->update([
                    'transaction_ref' => $response['paymentID'],
                    'invoice_number'  => $merchantInvoiceNumber,
                    'payment_gateway' => 'nagad',
                    'payment_status'  => 'pending',
                ]);
            }

            return response()->json([
                'payment_id' => $response['paymentID'] ?? null,
                'nagad_url'  => $response['nagadURL'] ?? $response['paymentURL'] ?? null,
                'raw'        => $response,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function callback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'status'     => 'required|string|max:255',
            'payment_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $paymentId = $request->payment_id;
        $status = strtolower($request->status);

        $order = Order::where('transaction_ref', $paymentId)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Fixed: Query Nagad Credentials instead of bKash
        $nagad = PaymentGateway::where('slug', 'nagad')->firstOrFail();
        $credentials = $nagad->auth_credentials ? json_decode($nagad->auth_credentials, true) : [];
        $credentials['is_test_mode'] = (bool) $nagad->is_test_mode;

        if ($status === 'success' || $status === 'successful') {
            try {
                // Fixed: Map to exact existing method signature 'verifyPayment'
                $execute = $this->nagadService->verifyPayment($paymentId, $credentials);

                if (isset($execute['transactionStatus']) && ($execute['transactionStatus'] === 'Completed' || $execute['transactionStatus'] === 'Success')) {
                    $order->update([
                        'payment_status'  => 'paid',
                        'transaction_ref' => $execute['trxID'] ?? $paymentId,
                    ]);

                    return response()->json([
                        'status'  => true,
                        'message' => 'Payment processed successfully',
                        'data'    => [
                            'order_id'   => $order->id,
                            'payment_id' => $paymentId,
                            'trx_id'     => $execute['trxID'] ?? null,
                            'amount'     => $execute['amount'] ?? null,
                            'currency'   => $execute['currency'] ?? null,
                            'raw'        => $execute,
                        ]
                    ], 200);
                }
            } catch (Exception $e) {
                $order->update(['payment_status' => 'failed']);
                return response()->json(['status' => false, 'message' => 'Verification failed: ' . $e->getMessage()], 200);
            }
        }

        // Handle negative workflow cleanly
        $finalStatus = match($status) {
            'cancel'  => 'cancelled',
            'failure' => 'failed',
            default   => 'failed'
        };

        $order->update(['payment_status' => $finalStatus]);

        return response()->json([
            'status'  => false,
            'message' => "Payment finished with status: {$finalStatus}",
        ], 200);
    }

    public function paymentStatus(Request $request)
    {
        if (!$request->has('payment_id')) {
            return response()->json(['status' => false, 'message' => 'paymentID is required'], 422);
        }

        $order = Order::where('transaction_ref', $request->payment_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $nagad = PaymentGateway::where('slug', 'nagad')->firstOrFail();
        $credentials = $nagad->auth_credentials ? json_decode($nagad->auth_credentials, true) : [];
        $credentials['is_test_mode'] = (bool) $nagad->is_test_mode;

        try {
            $response = $this->nagadService->queryPayment($request->payment_id, $credentials);

            return response()->json([
                'status'  => true,
                'message' => 'Payment status fetched successfully',
                'data'    => [
                    'order_id'           => $order->id,
                    'payment_id'         => $request->payment_id,
                    'trx_id'             => $response['trxID'] ?? null,
                    'transaction_status' => $response['transactionStatus'] ?? null,
                    'payment_status'     => $order->payment_status,
                    'amount'             => $response['amount'] ?? null,
                    'currency'           => $response['currency'] ?? null,
                    'raw'                => $response,
                ]
            ]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}