<?php

namespace Modules\Wallet\App\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Wallet\app\Models\WalletWithdrawalsTransaction;
use Modules\Wallet\app\Transformers\AdminWithdrawListResource;
use Modules\Wallet\app\Transformers\WalletWithdrawActivityResource;

class AdminWithdrawManageController extends Controller
{
    public function withdrawAllList(Request $request)
    {
        $query = WalletWithdrawalsTransaction::with('owner');

        $ownerType = User::class;

        // Apply filters if provided
        if (!empty($request->owner_id)) {
            $query->where('owner_id', $request->owner_id);
        }

        if (isset($request->owner_type)) {
            $query->where('owner_type', $ownerType);
        }

        if (!empty($request->amount)) {
            $query->where('amount', $request->amount);
        }

        if (!empty($request->status)) {
            $query->where('status', $request->status);
        }


        if (!empty($request->start_date) && !empty($request->end_date)) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $withdraws = $query->orderBy('created_at', "desc")->paginate($request->per_page ?? 10);


        if ($withdraws->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'messages.data_found',
                'data' => AdminWithdrawListResource::collection($withdraws),
                'meta' => new PaginationResource($withdraws)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'messages.data_not_found',
            ]);
        }
    }

    public function withdrawDetails(Request $request)
    {
        $WithdrawalRecord = WalletWithdrawalsTransaction::with('owner', 'wallet')->find($request->id);
        if ($WithdrawalRecord) {
            return response([
                'status' => true,
                'data' => new AdminWithdrawListResource($WithdrawalRecord),
                'activity' => new WalletWithdrawActivityResource($WithdrawalRecord),
            ]);
        } else {
            return response([
                'status' => false,
                'message' => __('messages.data_not_found'),
                'data' => null
            ]);
        }
    }
}
