<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function myWallet(Request $request)
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
        if ($user->activity_scope === 'store_level') {
            if (!$request->store_id && empty($request->store_id)) {
                return response()->json([
                    'message' => 'Store ID is required'
                ], 422);
            }
            $store = Branch::find($request->store_id);

            $wallets = Wallet::forOwner($store)->first();
        } else {
            $wallets = Wallet::forOwner($user)->first();
        }

        $wallet_settings = com_option_get('max_deposit_per_transaction');

        return response()->json([
            'wallets' => $wallets ? new UserWalletDetailsResource($wallets) : [],
            'max_deposit_per_transaction' => $wallet_settings,
        ]);
    }

    public function depositCreate(Request $request)
    {

        $wallet_settings = com_option_get('max_deposit_per_transaction');
        if (is_null($wallet_settings)) {
            $wallet_settings = 50000;
        }

        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:1|max:' . $wallet_settings, // Adding max limit based on wallet settings
            'transaction_details' => 'nullable|string',
            'transaction_ref' => 'nullable|string|unique:wallet_transactions,transaction_ref',
            'type' => 'nullable|string',
            'purpose' => 'nullable|string',
            'payment_gateway' => 'required|string',
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()
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

        // find user wallet
        $wallet = Wallet::where('id', $validated['wallet_id'])
            ->where('owner_id', $user->id)
            ->first();

        // Check if validation failed
        if (empty($wallet)) {
            return response()->json([
                'message' => 'wallet not found',
            ], 404);
        }

        try {
            // Create
            $wallet_history = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $validated['amount'],
                'type' => 'credit',
                'purpose' => 'deposit',
                'payment_gateway' => $request->payment_gateway,
                'payment_status' => 'pending',
                'status' => 0,
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
            "type" => $request->type
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
        if ($user->activity_scope === 'store_level') {
            if (!$request->store_id || empty($request->store_id)) {
                return response()->json([
                    'message' => 'Store ID is required'
                ], 422);
            }
            $store = Branch::find($request->store_id);
            $wallet = Wallet::forOwner($store)->first();

        } else {
            $wallet = Wallet::forOwner($user)->first();
        }
        if (!$wallet) {
            return response()->json([
                'message' => __('messages.wallet_not_found')
            ],404);
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

        $transactions = $query->where('wallet_id', $wallet->id)->paginate($filters['per_page'] ?? 10); // Change 10 to desired per-page limit
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

        // Get necessary details
        $customerEmail = $user->email ?? '';
        $providedHmac = $request->header('X-HMAC');
        $secretKey = '4b3403665fea6e60060fca1953b6e1eaa5c4dc102174f7e923217b87df40523a';

        // Generate the HMAC for comparison
        $calculatedHmac = hash_hmac('sha256', $customerEmail, $secretKey);

        // Verify HMAC
        if (!hash_equals($providedHmac, $calculatedHmac)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Key Not Match'
            ], 403);
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
