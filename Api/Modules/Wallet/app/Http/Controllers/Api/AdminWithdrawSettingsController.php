<?php

namespace Modules\Wallet\App\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use App\Models\WithdrawGateway;
use Illuminate\Http\Request;

class AdminWithdrawSettingsController extends Controller
{
    public function withdrawSettings(Request $request)
    {
            if ($request->isMethod('POST')) {
                $this->validate($request, [
                    'minimum_withdrawal_limit' => 'nullable|numeric|lte:maximum_withdrawal_limit',
                    'maximum_withdrawal_limit' => 'nullable|numeric|gte:minimum_withdrawal_limit',
                ]);

                $fields = [
                    'minimum_withdrawal_limit',
                    'maximum_withdrawal_limit',
                   ];

                foreach ($fields as $field) {
                    $value = $request->input($field) ?? null;
                    com_option_update($field, $value);
                }
                return $this->success(translate('messages.update_success', ['name' => 'Withdraw Settings']));
            }else{

                $minimum_withdrawal_limit = com_option_get('minimum_withdrawal_limit');
                $maximum_withdrawal_limit = com_option_get('maximum_withdrawal_limit');

                return $this->success([
                    'minimum_withdrawal_limit' => $minimum_withdrawal_limit,
                    'maximum_withdrawal_limit' => $maximum_withdrawal_limit
                ]);
            }
        }

    public function index()
    {
        $methods = WithdrawGateway::all();
        return response()->json([
            'status' => 'success',
            'data' => $methods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'nullable|json',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $method = WithdrawGateway::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw gateway created successfully.',
            'data' => $method,
        ], 201);
    }

    public function show($id)
    {
        $method = WithdrawGateway::find($id);

        if (!$method) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw gateway not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $method,
        ]);
    }
    public function update(Request $request, $id)
    {
        $method = WithdrawGateway::find($id);

        if (!$method) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw gateway not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fields' => 'nullable|json',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $method->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw gateway updated successfully.',
            'data' => $method,
        ]);
    }

    public function statusChange($id)
    {
        $method = WithdrawGateway::find($id);

        if (!$method) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw gateway not found.',
            ], 404);
        }

        $method->status = !$method->status;
        $method->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw gateway status updated successfully.',
            'data' => ['status' => $method->status],
        ]);
    }

    /**
     * Remove the specified withdraw gateway.
     */
    public function destroy($id)
    {
        $method = WithdrawGateway::find($id);

        if (!$method) {
            return response()->json([
                'status' => 'error',
                'message' => 'Withdraw gateway not found.',
            ], 404);
        }

        $method->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Withdraw gateway deleted successfully.',
        ]);
    }
}
