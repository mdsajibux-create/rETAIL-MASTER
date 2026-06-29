<?php

namespace Modules\Wallet\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Models\WithdrawGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Wallet\app\Transformers\WithdrawGatewayDetailsResource;
use Modules\Wallet\app\Transformers\WithdrawGatewayListResource;

class AdminWithdrawGatewayManageController extends Controller
{
    public function withdrawGatewayAdd(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'status' => 'nullable|integer|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $request['fields'] = json_encode($request['fields']);
        $success = WithdrawGateway::create($request->all());

        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => __('messages.save_success', ['name' => 'Gateway']),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.save_failed', ['name' => 'Gateway']),
            ]);
        }
    }

    public function withdrawGatewayList(Request $request)
    {
        $gateways = WithdrawGateway::paginate($request->per_page ?? 10);
        if ($gateways->count() > 0) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => WithdrawGatewayListResource::collection($gateways),
                'meta' => new PaginationResource($gateways)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }

    public function withdrawGatewayDetails(Request $request)
    {
        $gateway = WithdrawGateway::find($request->id);
        if (!empty($gateway)) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new WithdrawGatewayDetailsResource($gateway)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }

    public function withdrawGatewayUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:withdraw_gateways,id',
            'name' => 'required|string|max:255',
            'fields' => 'nullable|array',
            'status' => 'nullable|integer|in:0,1',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $request['fields'] = json_encode($request['fields']);
        $gateway = WithdrawGateway::findorfail($request->id);
        $success = $gateway->update($request->all());
        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => __('messages.update_success', ['name' => 'Gateway']),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.update_failed', ['name' => 'Gateway']),
            ]);
        }
    }

    public function withdrawGatewayDelete($id)
    {
        $gateway = WithdrawGateway::findorfail($id);
        if ($gateway) {
            $gateway->delete();
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.delete_success', ['name' => 'Gateway']),
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.delete_failed', ['name' => 'Gateway']),
            ]);
        }
    }

    public function withdrawGatewayChangeStatus(Request $request)
    {
        $gateway = WithdrawGateway::findorfail($request->id);
        if ($gateway) {
            $gateway->update([
                'status' => $gateway->status == 1 ? 0 : 1
            ]);
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Gateway Status']),
            ]);
        }else{
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found'),
            ]);
        }
    }
}
