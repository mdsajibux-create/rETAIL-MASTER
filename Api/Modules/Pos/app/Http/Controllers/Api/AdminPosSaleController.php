<?php

namespace Modules\Pos\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Pos\app\Http\Requests\PosOrderRequest;
use Modules\Pos\app\Interfaces\PosInterface;
use Modules\Pos\app\Services\PosOrderService;


class AdminPosSaleController extends Controller
{
    protected $pos;
    protected PosOrderService $posOrderService;

    public function __construct(PosInterface $pos, PosOrderService $posOrderService)
    {
        $this->pos = $pos;
        $this->posOrderService = $posOrderService;
    }

    public function createOrder(PosOrderRequest $request)
    {
        if (empty($request->branch_id)){
            return response()->json([
                'status' => false,
                'message' => 'Please select branch'
            ]);
        }

        return $this->posOrderService->placeOrder($request);
    }

    public function listProducts(Request $request)
    {
        return $this->pos->getProducts($request);
    }

    public function getProductBySlug(Request $request, $slug)
    {
        return $this->pos->getProductBySlug($request, $slug);
    }

    public function addCustomer(Request $request)
    {
        return $this->pos->createNewCustomer($request);
    }

    public function listCustomers(Request $request)
    {
        $filters = [
            'search' => $request->search,
        ];

        $branch_id = $request->branch_id;

        return $this->pos->getStoreCustomers($branch_id, $filters);
    }

    public function posSettings(Request $request)
    {
        $fields = [
            'com_pos_settings_print_invoice',
        ];

        $settings = collect($fields)->mapWithKeys(function ($field) {
            return [$field => com_option_get($field)];
        });

        return response()->json([
            'data' => $settings
        ], 200);
    }

    public function updatePosSettings(Request $request)
    {
        // list of allowed pos settings
        $fields = [
            'com_pos_settings_print_invoice',
        ];

        $validator = Validator::make($request->all(), [
            'com_pos_settings_print_invoice' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Save all settings dynamically
        foreach ($fields as $field) {
            $value = $request->input($field) ?? null;
            com_option_update($field, $value);
        }

        return $this->success(translate('messages.update_success',
            ['name' => 'POS Settings']
        ));

    }

    public function invoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        return $this->posOrderService->getInvoice($request->order_id);
    }

    public function orders(Request $request)
    {
        return $this->pos->getOrders($request);
    }

}
