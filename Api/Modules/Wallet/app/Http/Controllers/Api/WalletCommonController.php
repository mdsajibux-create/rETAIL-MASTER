<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Helpers\ComHelper;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Modules\Branch\app\Models\Branch;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;
use Modules\Wallet\app\Models\WalletWithdrawalsTransaction;
use Modules\Wallet\app\Transformers\UserWalletDetailsResource;
use Modules\Wallet\app\Transformers\WalletHistoryResource;
use Modules\Wallet\app\Transformers\WalletTransactionListResource;

class WalletCommonController extends Controller
{
    public function myWalletInfo(Request $request)
    {
        // check which guard is being used
        if (auth()->guard('api_customer')->check()) {
            $user = auth()->guard('api_customer')->user();
        } elseif (auth()->guard('api')->check()) {
            $user = auth()->guard('api')->user();
        }

        // If no user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        //  wallets for the authenticated user
        $wallets = Wallet::forOwner($user)->first();


        $wallet_settings = com_option_get('max_deposit_per_transaction');

        return response()->json([
            'wallets' => $wallets ? new UserWalletDetailsResource($wallets) : null,
            'max_deposit_per_transaction' => $wallet_settings,
        ]);
    }

    public function depositCreate(Request $request)
    {
        $currencyData = ComHelper::getCurrencyInfo($request->currency_code);
        // Max deposit per transaction in system currency
        $maxDepositSystem = com_option_get('max_deposit_per_transaction') ?? 50000;
        // Convert max deposit into user selected currency
        $maxDepositUserCurrency = round($maxDepositSystem * $currencyData['exchange_rate'], 2);


        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => [
                'required',
                'numeric',
                'min:1',
                'max:' . $maxDepositUserCurrency
            ],
            'transaction_details' => 'nullable|string',
            'transaction_ref' => 'nullable|string|unique:wallet_transactions,transaction_ref',
            'type' => 'nullable|string',
            'purpose' => 'nullable|string',
            'payment_gateway' => 'required|string',
            'currency_code' => 'required|string|max:3',
        ],
            [
                'amount.max' => 'The amount must not be greater than '
                    . amount_with_symbol_format_for_response($maxDepositUserCurrency, $currencyData['currency_code']),
                'amount.min' => 'The amount must be at least 1 ' . $currencyData['currency_code'],
            ]
        );

        // wallet main amount store in site default currency
        $userAmount = $request->amount;
        if ($currencyData['currency_code'] === $currencyData['default_currency']) {
            // Already in system currency
            $amountInSystemCurrency = $userAmount;
        } else {
            // Convert to system currency
            $amountInSystemCurrency = round($userAmount / $currencyData['exchange_rate'], 2);
        }


        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (shouldRound() && is_float($request->amount)) {
            return response()->json([
                'message' => __('wallet::messages.should_round', ['name' => strtoupper($request->amount)])
            ], 422);
        }

        $validated = $validator->validated();

        // auth user check
        if (auth()->guard('api_customer')->check()) {
            $user = auth()->guard('api_customer')->user();
        } elseif (auth()->guard('api')->check()) {
            $user = auth()->guard('api')->user();
        }

        // If no user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

         $wallet = Wallet::where('owner_id',  $user->id)
             ->where('owner_type', 'App\Models\Customer')
             ->first();

         if (empty($wallet)){
             return response()->json([
                 'message' => __('wallet::messages.wallet_not_found'),
             ], 422);
         }

         $wallet_id = $wallet->id;


        if ($wallet->status === 0) {
            return response()->json([
                'message' => __('wallet::messages.wallet_inactive')
            ], 422);
        }

        // Check if validation failed
        if (empty($wallet)) {
            return response()->json([
                'message' => 'wallet not found',
            ], 404);
        }

        try {
            $currencyData = ComHelper::getCurrencyInfo($request->currency_code);
            // Create
            $wallet_history = WalletTransaction::create([
                'wallet_id' => $wallet_id,
                'amount' => $amountInSystemCurrency,
                'type' => 'credit',
                'purpose' => 'deposit',
                'payment_gateway' => $request->payment_gateway,
                'payment_status' => 'pending',
                'status' => 0,
                'currency_code' => $currencyData['currency_code'],
                'exchange_rate' => $currencyData['exchange_rate']
            ]);

            $wallet_history_id = $wallet_history->id;

            return response()->json([
                'message' => 'Deposit created successfully',
                'wallet_history_id' => $wallet_history_id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create deposit',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }


    public function transactionRecords(Request $request)
    {
        $filters = [
            "start_date" => $request->start_date,
            "end_date" => $request->start_date,
            "search" => $request->search,
            "status" => $request->status,
            "payment_status" => $request->payment_status,
            "type" => $request->type,
            "per_page" => $request->per_page
        ];

        // auth user check
        if (auth()->guard('api_customer')->check()) {
            $user = auth()->guard('api_customer')->user();
        } elseif (auth()->guard('api')->check()) {
            $user = auth()->guard('api')->user();
        }

        // If no user is authenticated
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // user's wallet
        $wallet = Wallet::forOwner($user)->first();

        if (!$wallet) {
            return response()->json([
                'message' => __('messages.wallet_not_found')
            ], 404);
        }

        $query = WalletTransaction::query();

        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
                // Filter by date range
                $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
            } else {
                // Filter by a single exact date
                $date = $filters['start_date'] ?? $filters['end_date'];
                $query->whereDate('created_at', $date);
            }
        }

        if (!empty($filters['search'])) {
            $query->where('transaction_ref', 'LIKE', '%' . $filters['search'] . '%');
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        $transactions = $query->where('wallet_id', $wallet->id)->latest()->paginate($filters['per_page'] ?? 10); // Change 10 to desired per-page limit
        return response()->json([
            'wallets' => WalletTransactionListResource::collection($transactions),
            'meta' => new PaginationResource($transactions),
        ]);
    }

    public function paymentStatusUpdate(Request $request)
    {
        // Check if the user is authenticated
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized'
            ], 401);
        }

        // Validate the required inputs using Validator::make
        $validated = Validator::make($request->all(), [
            'wallet_history_id' => 'required',
            'transaction_ref' => 'nullable|string|max:255',
            'transaction_details' => 'nullable|string|max:1000',
        ]);

        // Check if validation fails
        if ($validated->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validated->errors(),
            ], 400);
        }


        // Find the wallet history
        $wallet_history = WalletTransaction::where('id', $request->wallet_history_id)->first();

        // Check if the payment status is already marked as 'paid'
        if ($wallet_history->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'The payment gateway status is already marked as paid.'
            ], 403);
        }

        $wallet = Wallet::where('id', $wallet_history->wallet_id)->first();

        // Check if the wallet history exists
        if (empty($wallet_history)) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet not found'
            ], 404);
        }

        // Update the wallet history
        $wallet_history->update([
            'payment_status' => 'paid',
            'transaction_ref' => $request->transaction_ref ?? null,
            'transaction_details' => $request->transaction_details ?? null,
            'status' => 1,
        ]);

        // Update the wallet balance
        $wallet->balance += $wallet_history->amount;
        $wallet->save();

        // Return success response
        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully',
        ]);
    }

    public function walletHistory(Request $request)
    {
        // Check authentication
        if (!auth('api')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access, please log in.',
            ], 401);
        }

        $user = auth('api')->user();

        // Validate activity scope
        if (!in_array($user->activity_scope, ['store_level', 'delivery_level'])) {
            return unauthorized_response();
        }
        $ownerType = 'App\Models\User';
        if ($user->activity_scope == 'store_level') {
            $ownerType = 'Modules\Branch\app\Models\Branch';
        }

        // Fetch wallet data
        $user_wallet = Wallet::where('owner_id', $user->id)->where('owner_type', $ownerType)->first();
        if (!$user_wallet) {
            return response()->json([
                'status' => false,
                'message' => __('messages.data_not_found'),
            ], 404);
        }

        // Fetch earning history
        $earningHistory = WalletTransaction::where('wallet_id', $user_wallet->id)
            ->where('type', 'credit')
            ->where('status', 1)
            ->latest()
            ->get();

        // Fetch latest withdrawal history
        $withdrawHistory = WalletWithdrawalsTransaction::where('wallet_id', $user_wallet->id)->orderBy('approved_at', 'asc')->get();
        return response()->json([
            'status' => true,
            'message' => __('messages.data_found'),
            'data' => new WalletHistoryResource($earningHistory, $withdrawHistory),
        ]);
    }

}
