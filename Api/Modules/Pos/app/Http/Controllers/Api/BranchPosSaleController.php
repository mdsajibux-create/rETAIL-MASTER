<?php

namespace Modules\Pos\app\Http\Controllers\Api;

use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Pos\app\Http\Requests\PosOrderRequest;
use Modules\Pos\app\Interfaces\PosInterface;
use Modules\Pos\app\Services\PosOrderService;

class BranchPosSaleController extends Controller
{
    protected object $seller;
    protected object $store;
    protected PosInterface $pos;
    protected PosOrderService $posOrderService;

    public function __construct(PosInterface $pos, PosOrderService $posOrderService)
    {
        $this->pos = $pos;
        $this->posOrderService = $posOrderService;

        // Check  pos
       $this->middleware(function ($request, $next) {
       $authUser = auth('api')->user();

        if ($authUser->activity_scope !== 'branch_level') {
            return response()->json([
                'message' => __('pos::messages.permission_denied')
            ], 403);
        }

            return $next($request);
        });
    }

    public function createOrder(PosOrderRequest $request)
    {
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
        $branch_id  = auth('api')->user()->branch_id;

        // Add branch_id to request data
        $request->merge([
            'branch_id' => $branch_id
        ]);

        return $this->pos->createNewCustomer($request);
    }

    public function listCustomers(Request $request)
    {
        $filters = [
            'search' => $request->search,
        ];

        $branch_id = auth('api')->user()->branch_id;

        return $this->pos->getStoreCustomers($branch_id, $filters);
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
