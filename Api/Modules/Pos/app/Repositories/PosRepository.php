<?php

namespace Modules\Pos\app\Repositories;

use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\PosCustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Transformers\AdminOrderResource;
use Modules\Order\app\Transformers\OrderSummaryResource;
use Modules\Pos\app\Interfaces\PosInterface;
use Modules\Pos\app\Models\PosCustomer;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Transformers\ProductDetailsPublicResource;
use Modules\Product\app\Transformers\ProductPosResource;
use Modules\Product\app\Transformers\ProductPublicResource;

class PosRepository implements PosInterface
{
    public function getStoreCustomers($branch_id, $filters)
    {
        $orderCustomerIds = Order::pluck('customer_id');
        $posCustomerIds = PosCustomer::pluck('customer_id');

        $allCustomerIds = $orderCustomerIds
            ->merge($posCustomerIds)
            ->unique()
            ->values();

        $query = Customer::with('wallet')
            ->whereIn('id', $allCustomerIds);

        if ($filters['search']) {
            $searchTerm = $filters['search'];
            $query->where(function ($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
            });
        }

        $customers = $query->limit(50)->get();

        return response()->json([
            'data' => PosCustomerResource::collection($customers),
        ]);
    }

    public function getProducts(Request $request)
    {

        $authUser = auth('api')->user();
        $branchId = $authUser?->branch_id ? (int) $authUser->branch_id : (int) $request->branch_id;

        if ($authUser->activity_scope === 'system_level' && empty($request->branch_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Branch id is required'
            ]);
        }


        // check theme, branch wise get stock . only is_web branch wise get stock
        $query = Product::query()
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->whereHas('stocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            })
            ->with(['stocks' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            }]);


        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->search . '%');
            });
        }

        if (isset($request->category_id)) {
            $query->where('products.category_id', $request->category_id);
        }

        $perPage = $request->per_page ?? 10;



        $products = $query->with([
            'category',
            'unit',
            'tags',
            'brand',
            // branch wise main product stock
            'stocks' => function ($q) use ($branchId) {
                if ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                }
            },

            // branch wise variant stock
            'variants' => function ($query) use ($request, $branchId) {
                $shouldRound = shouldRound();

                $discountAmountExpr = $shouldRound
                    ? 'ROUND(fs1.discount_amount)'
                    : 'fs1.discount_amount';

                $discountSpecialPricePercentExpr = $shouldRound
                    ? 'ROUND(product_variants.special_price * fs1.discount_amount / 100)'
                    : '(product_variants.special_price * fs1.discount_amount / 100)';

                $discountBasePricePercentExpr = $shouldRound
                    ? 'ROUND(product_variants.price * fs1.discount_amount / 100)'
                    : '(product_variants.price * fs1.discount_amount / 100)';

                $priceExpr = "
                CASE
                    WHEN fsp1.id IS NOT NULL THEN
                        CASE fs1.discount_type
                            WHEN 'amount' THEN
                                CASE
                                    WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                        product_variants.special_price - $discountAmountExpr
                                    ELSE
                                        product_variants.price - $discountAmountExpr
                                END
                            WHEN 'percentage' THEN
                                CASE
                                    WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                        product_variants.special_price - $discountSpecialPricePercentExpr
                                    ELSE
                                        product_variants.price - $discountBasePricePercentExpr
                                END
                            ELSE
                                CASE
                                    WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                        product_variants.special_price
                                    ELSE
                                        product_variants.price
                                END
                        END
                    WHEN product_variants.special_price IS NOT NULL
                        AND product_variants.special_price > 0
                        AND product_variants.special_price < product_variants.price THEN
                        product_variants.special_price
                    ELSE
                        product_variants.price
                END
            ";

                $finalExpr = $shouldRound ? "ROUND($priceExpr)" : "FORMAT($priceExpr, 2)";

                $query->select('product_variants.*')
                    ->leftJoin('flash_sale_products as fsp1', 'fsp1.product_id', '=', 'product_variants.product_id')
                    ->leftJoin('flash_sales as fs1', 'fs1.id', '=', 'fsp1.flash_sale_id')
                    ->selectRaw("$finalExpr as effective_price")
                    ->with(['stocks' => function ($sq) use ($branchId) {
                        if ($branchId) {
                            $sq->where('branch_id', $branchId)
                                ->where('qty', '>', 0)
                                ->where('is_active', 1);
                        }
                    }]);

                if ($request->sort === 'price_low_high') {
                    $query->orderByRaw("$finalExpr ASC");
                } elseif ($request->sort === 'price_high_low') {
                    $query->orderByRaw("$finalExpr DESC");
                }
            },

            'related_translations'
        ])->paginate($perPage);

        return response()->json([
            'messages' => __('pos::messages.data_found'),
            'data' => ProductPosResource::collection($products),
            'meta' => new PaginationResource($products),
        ]);

    }

    public function getProductBySlug($request, $slug)
    {

        $user     = auth('api')->user();

        //  Only set branchId if user is branch level
        if ($user->activity_scope === 'branch_level' && $user->branch_id) {
            $branchId = $user->branch_id;
        }else{
            if (empty($request->branch_id)){
                return response()->json([
                    'status' => false,
                    'messages' => 'Required branch id',
                ]);
            }

            $branchId = (int) $request->branch_id;
        }

        $product = Product::with([
            'tags',
            'unit',
            'brand',
            'category',
            'related_translations',
            'variants',
            'variants.stock' => function ($query) use ($branchId) {
                if ($branchId) {
                    $query->where('branch_id', $branchId); //  branch-wise stock
                }
            },
        ])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where('slug', $slug)
            ->first();

        if (empty($product)){
            return response()->json([
               'status' => false,
               'message' => __('pos::messages.not_found', ['name' => 'Product']),
            ]);
        }

        return response()->json([
            'messages' => __('pos::messages.data_found'),
            'data' => new ProductDetailsPublicResource($product),
        ], 200);
    }

    public function createNewCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|string|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:255|unique:customers,phone',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $password = Str::random(8);
        $customer = \DB::transaction(function () use ($request, $password) {
            // create customer
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($password),
            ]);

            // create pos customer data
            PosCustomer::create([
                'customer_id' => $customer->id,
                'branch_id' => $request->branch_id,
            ]);

            return $customer;
        });

        return response()->json([
            'messages' => __('pos::messages.save_success', ['name' => 'Customer']),
            'customer' => $customer->makeHidden(['created_at', 'updated_at']),
        ]);
    }

    public function getOrders(Request $request)
    {
        $order_id = $request->order_id;

        if ($order_id) {
            $order = Order::with([
                'customer',
                'orderDetail.product',
                'zone',
                'deliveryman',
                'orderAddress',
                'refund',
                'refund.orderRefundReason',
                'orderActivities',
            ])
                ->where('id', $order_id)
                ->where('order_type', 'pos')
                ->first();

            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $deliveryman_id = $order->confirmed_by;

            $total_delivered = Order::where('confirmed_by', $deliveryman_id)
                ->where('status', 'delivered')
                ->count();

            $last_delivered_location = Order::with('orderAddress')
                ->where('confirmed_by', $deliveryman_id)
                ->where('status', 'delivered')
                ->orderBy('delivery_completed_at', 'desc')
                ->first();

            if ($order->deliveryman) {
                $order->deliveryman->last_delivered_location = optional($last_delivered_location?->orderAddress)->address ?? 'No address available';
                $order->deliveryman->total_delivered = $total_delivered ?? 0;
            }

            return response()->json([
                    'order_data' => new AdminOrderResource($order),
                    'order_summary' => new OrderSummaryResource($order),
                ]);
        }


       // check branch
        if(auth('api')->user()->activity_scope === 'branch_level' && auth('api')->user()->branch_id){
            $ordersQuery = Order::with([
                'customer',
                'orderDetail',
                'deliveryman',
                'orderAddress'
            ])->where('branch_id', auth('api')->user()->branch_id)
              ->where('order_type', 'pos');
        }else{
            $ordersQuery = Order::with([
                'customer',
                'orderDetail',
                'deliveryman',
                'orderAddress'
            ])->where('order_type', 'pos');
        }


        $ordersQuery->when($request->status, fn($query) => $query->where('status', $request->status));
        $ordersQuery->when($request->refund_status, fn($query) => $query->where('refund_status', $request->refund_status));

        $ordersQuery->when($request->start_date && $request->end_date, function ($query) use ($request) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        });

        $ordersQuery->when($request->payment_status, function ($query) use ($request) {
            $query->where('payment_status', $request->payment_status);
        });

        $ordersQuery->when($request->search, fn($query) => $query->where('id', 'LIKE', '%' . $request->search . '%')
            ->orWhere('invoice_number', 'LIKE', '%' . $request->search . '%'));

        $orders = $ordersQuery->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'orders' => AdminOrderResource::collection($orders),
            'meta' => new PaginationResource($orders),
            'status' =>[]
        ]);
    }
}