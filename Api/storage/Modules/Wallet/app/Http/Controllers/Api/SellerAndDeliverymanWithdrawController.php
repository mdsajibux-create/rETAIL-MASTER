<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Enums\WalletOwnerType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\WithdrawGatewayPublicListResource;
use App\Mail\DynamicEmail;
use App\Models\User;
use App\Models\WithdrawGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Modules\Branch\app\Models\Branch;
use Modules\SystemCore\app\Models\EmailTemplate;
use Modules\Wallet\app\Models\Wallet;
use Modules\Wallet\app\Models\WalletWithdrawalsTransaction;
use Modules\Wallet\app\Transformers\WithdrawDetailsResource;
use Modules\Wallet\app\Transformers\WithdrawListResource;

class SellerAndDeliverymanWithdrawController extends Controller
{
    public function withdrawGatewayList(Request $request)
    {
        $search = request()->input('search');
        $gateways = WithdrawGateway::where('status', 1)
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();
        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.data_found'),
            'data' => WithdrawGatewayPublicListResource::collection($gateways),
        ]);

    }

    public function withdrawAllList(Request $request)
    {
        // Check if the user is authenticated
        if (!auth('api')->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access, please log in.',
            ], 401);
        }

        $user = auth('api')->user();

        // Check if the user has the correct activity scope
        if (!in_array($user->activity_scope, ['store_level', 'delivery_level'])) {
            return unauthorized_response();
        }

        // Initialize query
        $query = WalletWithdrawalsTransaction::query();

        // Apply filters based on activity scope
        if ($user->activity_scope === 'store_level' && $request->filled('store_id')) {
            $query->where('owner_id', $request->store_id);
        } elseif ($user->activity_scope === 'delivery_level' && $request->filled('deliveryman_id')) {
            $query->where('owner_id', $request->deliveryman_id);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid request. Please provide a valid store_id or deliveryman_id.',
            ], 400);
        }

        // Apply additional filters
        if ($request->filled('amount')) {
            $query->where('amount', $request->amount);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Paginate results
        $withdraws = $query->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'message' => __('messages.data_found'),
            'data' => WithdrawListResource::collection($withdraws),
            'meta' => new PaginationResource($withdraws),
        ]);
    }


    public function withdrawDetails(Request $request)
    {

        $withdraw = WalletWithdrawalsTransaction::where('id', $request->id)->first();

        if (!empty($withdraw)) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new WithdrawDetailsResource($withdraw)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
                'data' => []
            ]);
        }
    }

    public function withdrawRequest(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            "store_id" => "nullable|exists:stores,id", // store exists
            "deliveryman_id" => "nullable|exists:users,id", // deliveryman exists
            "customer_id" => "nullable|exists:customers,id",
            "withdraw_gateway_id" => "required|integer|exists:withdraw_gateways,id",
            "amount" => "required",
            "details" => "nullable|string|max:2000",
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        if (isset($request->store_id)) {
            $owner_id = $request->store_id;
        } else if (isset($request->deliveryman_id)) {
            $owner_id = $request->deliveryman_id;
        } else if (isset($request->customer_id)) {
            $owner_id = $request->customer_id;
        }
        $withdraw_amount = $request->amount;

        $min_limit = com_option_get('minimum_withdrawal_limit');
        $max_limit = com_option_get('maximum_withdrawal_limit');
        if ($min_limit !== null || $max_limit !== null) {
            if ($withdraw_amount < intval($min_limit) || $withdraw_amount > intval($max_limit)) {
                return response()->json([
                    'message' => "Please enter a valid amount between " .
                        $min_limit . ' - ' .
                        $max_limit
                ], 422);
            }
        }

        // balance check
        if (isset($request->store_id)) {
            $ownerType = WalletOwnerType::STORE->value;
        } else if (isset($request->deliveryman_id)) {
            $ownerType = WalletOwnerType::DELIVERYMAN->value;
        } else if (isset($request->customer_id)) {
            $ownerType = WalletOwnerType::CUSTOMER->value;
        }

        $wallet = Wallet::where('owner_id', $owner_id)
            ->where('owner_type', $ownerType)
            ->first();

        if (empty($wallet)) {
            return response()->json([
                'message' => 'You have no wallet.',
            ], 422);
        }

        if (!empty($wallet) && $wallet->balance <= 0) {
            return response()->json([
                'message' => 'You have insufficient balance.',
            ], 422);
        }

        // Validate if store finances exist and current balance is sufficient
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'message' => 'You have insufficient balance.',
            ], 422);
        }

        $method = WithdrawGateway::find($request->withdraw_gateway_id);
        $success = WalletWithdrawalsTransaction::create([
            'wallet_id' => $wallet->id,
            'owner_id' => $owner_id,
            'owner_type' => $ownerType,
            'withdraw_gateway_id' => $method->id,
            'gateway_name' => $method->name,
            'amount' => $request->amount,
            'details' => $request->details ?? null,
            'fee' => 0.00,
            'gateways_options' => json_encode($request->gateways),
        ]);


        if ($success) {
            // mail send to admin
            try {
                $email_template_store_withdrawal_approved = EmailTemplate::where('type', 'store-withdrawal-request-to-admin')
                    ->where('status', 1)
                    ->first();

                $seller_info = User::where('id', auth()->guard('api')->user()->id)->first();
                $store_info = Branch::where('id', $request->store_id)->first();
                // store info
                $store_email = $store_info->email;
                $store_owner_name = $seller_info->full_name ?? '';
                $store_name = $store_info->name;
                $request_date = $success->created
                    ? Carbon::parse($success->created_at)->format('F d, Y')
                    : 'N/A';

                $store_subject = $email_template_store_withdrawal_approved->subject;
                $store_message = $email_template_store_withdrawal_approved->body;
                $withdraw_amount = amount_with_symbol_format((int)$request->amount);

                $store_message = str_replace(["@seller_name", "@store_name", "@amount", "@request_date"],
                    [
                        $store_owner_name,
                        $store_name,
                        $withdraw_amount,
                        $request_date,
                    ], $store_message);

                // customer
                Mail::to($store_email)->send(new DynamicEmail($store_subject, (string)$store_message));

            } catch (\Exception $th) {
            }

            return response()->json([
                'message' => __('messages.request_success', ['name' => 'Withdrawal']),
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.request_failed', ['name' => 'Withdrawal']),
            ], 500);
        }
    }
}
