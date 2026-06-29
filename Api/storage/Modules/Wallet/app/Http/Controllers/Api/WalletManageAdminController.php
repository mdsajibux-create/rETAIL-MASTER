<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletTransaction;
use Modules\Wallet\app\Transformers\WalletListResource;
use Modules\Wallet\app\Transformers\WalletTransactionListResource;

class WalletManageAdminController extends Controller
{
    public function depositSettings(Request $request)
    {
        if ($request->isMethod('post')) {
            com_option_update('max_deposit_per_transaction', $request->max_deposit_per_transaction);
            return response()->json(['message' => 'Wallet settings successfully']);
        }
        $wallet_settings = com_option_get('max_deposit_per_transaction');
        return response()->json([
            'wallet_settings' => $wallet_settings
        ]);
    }

    public function index(Request $request)
    {

        $wallets = Wallet::query();

        // Filter by owner_id if provided
        if ($request->has('owner_id') && $request->input('owner_id') !== '') {
            $wallets->where('owner_id', $request->input('owner_id'));
        }

        // Filter by owner_type if provided
        $wallet_type = $request->has('owner_type') ?
            ($request->input('owner_type') === 'customer' ? 'App\Models\Customer' :
                ($request->input('owner_type') === 'user' ? 'App\Models\User' : 'all')) :
            'all';

        // Apply the filter if the owner_type is valid and not 'all'
        if ($wallet_type !== 'all') {
            $wallets->where('owner_type', $wallet_type);
        }

        // Filter by status if provided
        if (!empty($request->status)) {
            $wallets->where('status', (int)$request->status);
        }

        // Paginate the results with a default of 10 per page
        $wallets = $wallets->latest()->paginate($request->per_page ?? 500);

        return response()->json([
            'wallets' => WalletListResource::collection($wallets),
            'pagination' => new PaginationResource($wallets)
        ]);
    }

    public function status(Request $request)
    {
        $wallet = Wallet::findOrFail($request->id);
        $wallet->status = !$wallet->status;
        $wallet->save();

        return response()->json([
                'message' => 'Deposit status changed successfully']
        );
    }

    public function depositCreateByAdmin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'required|exists:wallets,id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_details' => 'nullable|string',
            'transaction_ref' => 'nullable|string|unique:wallet_transactions,transaction_ref',
            'type' => 'nullable|string',
            'purpose' => 'nullable|string',
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $validated = $validator->validated();

        try {
            // Find the wallet where the deposit will be made
            $wallet = Wallet::findOrFail($validated['wallet_id']);
            // Update the wallet balance
            $wallet->balance += $validated['amount'];
            $wallet->save();

            // Create
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_ref' => $validated['transaction_ref'] ?? 'TXN' . strtoupper(uniqid()),
                'transaction_details' => $validated['transaction_details'] ?? 'Admin deposit',
                'amount' => $validated['amount'],
                'type' => 'credit',
                'purpose' => 'deposit',
                'status' => 1,
            ]);
            return response()->json([
                    'message' => 'Deposit created successfully']
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create deposit',
                'error' => 'An unexpected error occurred. Please try again later.'
            ], 500);
        }
    }


    public function transactionRecords(Request $request)
    {

        // Get the start and end date from the request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = WalletTransaction::with('wallet.owner');

        // transactions by date range
        if ($startDate && $endDate) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();
            // Apply the date filter
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Order by latest first
        $query->orderBy('created_at', 'DESC');

        // Paginate
        $transactions = $query->paginate($request->per_page ?? 10);

        return response()->json([
            'wallets' => WalletTransactionListResource::collection($transactions),
            'pagination' => new PaginationResource($transactions),
        ]);
    }

    public function transactionStatus(Request $request)
    {
        // specific transaction
        $transaction = WalletTransaction::findOrFail($request->id);
        $transaction->status = !$transaction->status;
        $transaction->save();
        return response()->json(['message' => 'Transaction status changed successfully']);
    }

    public function transactionPaymentStatusChange(Request $request)
    {

        $validated = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:wallet_transactions,id',
            'payment_status' => 'required|in:pending,paid,failed',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validated->errors(),
            ], 422);
        }

        $transaction = WalletTransaction::findOrFail($request->transaction_id);

        // Avoid re-processing already paid transactions
        if ($transaction->status === 'paid' && $request->payment_status === 'paid') {
            return response()->json([
                'message' => 'Transaction is already marked as paid.',
            ], 200);
        }

        // Update transaction status
        $transaction->payment_status = $request->payment_status;
        $transaction->status = 1;
        $transaction->save();

        // Update wallet balance only if status is now "paid" and wasn't already
        if ($request->payment_status === 'paid') {
            $wallet = Wallet::findOrFail($transaction->wallet_id);
            $wallet->balance += $transaction->amount;
            $wallet->save();
        }


        return response()->json([
            'message' => 'Transaction payment status updated successfully',
            'transaction' => $transaction,
        ]);
    }
}
