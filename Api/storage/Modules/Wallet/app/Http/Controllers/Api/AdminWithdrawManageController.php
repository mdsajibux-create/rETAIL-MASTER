<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Models\WithdrawalRecord;
use Illuminate\Http\Request;
use Modules\Wallet\app\Transformers\AdminWithdrawDetailsResource;
use Modules\Wallet\app\Transformers\AdminWithdrawListResource;

class AdminWithdrawManageController extends Controller
{
    public function withdrawAllList(Request $request)
    {
        if (auth('api')->user()->activity_scope !== 'system_level') {
            return unauthorized_response();
        }

        $query = WithdrawalRecord::with(['user', 'withdrawGateway']);

        if (isset($request->user)) {
            $query->whereHas('user', function ($userQuery) use ($request) {
                $userQuery->where('first_name', 'like', "%{$request->user}%")
                ->orWhere('last_name','like',"%{$request->user}%");
            });
        }

        if (isset($request->amount)) {
            $query->where('amount', $request->amount);
        }

        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        if (isset($request->start_date) && isset($request->end_date)) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $withdraws = $query->latest()->paginate($request->per_page ?? 10);

        if ($withdraws->isNotEmpty()) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => AdminWithdrawListResource::collection($withdraws),
                'meta' => new PaginationResource($withdraws)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }

    public function withdrawDetails(Request $request)
    {
        if (auth('api')->user()->activity_scope !== 'system_level') {
            unauthorized_response();
        }
        $withdraw = WithdrawalRecord::with(['user', 'withdrawGateway'])->find($request->id);
        if (!empty($withdraw)) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new AdminWithdrawDetailsResource($withdraw)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }
}
