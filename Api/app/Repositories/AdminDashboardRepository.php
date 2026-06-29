<?php

namespace App\Repositories;

use App\Interfaces\AdminDashboardInterface;
use App\Models\Customer;
use Modules\SupportTicket\app\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Blog\app\Models\Blog;
use Modules\Branch\app\Models\Branch;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\Catalog\app\Models\ProductBrand;
use Modules\Catalog\app\Models\ProductCategory;
use Modules\Coupon\app\Models\Coupon;
use Modules\Order\app\Models\Order;
use Modules\Order\app\Models\OrderDetail;
use Modules\Product\app\Models\Product;
use Modules\SystemCore\app\Models\Page;
use Modules\Wallet\app\Models\WalletWithdrawalsTransaction;

class AdminDashboardRepository implements AdminDashboardInterface
{
    public function __construct(protected User $user)
    {

    }

    public function getSummaryData(?int $branch_id = null, $filters = [])
    {
        $storeQuery = Branch::query();
        $userQuery = User::query();
        $orderQuery = Order::query();
        $total_branch = $storeQuery->count();
        $total_product = Product::count();
        $total_customer = Customer::count();

        $baseOrderQuery = $orderQuery;

        // find branch id wise
        if ($branch_id) {
            $baseOrderQuery->where('branch_id', $branch_id);
        }

        $total_order = (clone $baseOrderQuery)->count();
        $pending_orders = (clone $baseOrderQuery)->where('status', 'pending')->count();
        $confirmed_orders = (clone $baseOrderQuery)->where('status', 'confirmed')->count();
        $processing_orders = (clone $baseOrderQuery)->where('status', 'processing')->count();
        $shipped_orders = (clone $baseOrderQuery)->where('status', 'shipped')->count();
        $completed_orders = (clone $baseOrderQuery)->where('status', 'delivered')->count();
        $cancelled_orders = (clone $baseOrderQuery)->where('status', 'cancelled')->count();
        $deliveryman_not_assigned_orders = (clone $baseOrderQuery)
            ->where('status', 'processing')
            ->whereNull('confirmed_by')
            ->count();

        $refunded_orders = (clone $baseOrderQuery)->where('refund_status', 'refunded')->count();

        $total_deliverymen = User::where('activity_scope', 'delivery_level')->count();
        $total_categories = ProductCategory::count();
        $total_brands = ProductBrand::count();
        $total_coupons = Coupon::count();
        $total_blogs = Blog::count();
        $total_tickets = Ticket::count();

        // Total Order Amount calculation
        $total_order_amount_without_pos = (clone $baseOrderQuery)->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled'])
            ->where('order_type', '!=', 'pos')
            ->where(function ($q) {
                $q->where('refund_status', '!=', 'refunded')
                    ->orWhereNull('refund_status');
            })->sum('order_amount');

        // Total Order Amount calculation
        $total_order_amount_query = (clone $baseOrderQuery)->where('payment_status', 'paid')
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($q) {
                $q->where('refund_status', '!=', 'refunded')
                    ->orWhereNull('refund_status');
            });

        $total_order_amount_query = $total_order_amount_query->sum('order_amount');

        $pos_earningsQuery = (clone $baseOrderQuery)->where('payment_status', 'paid')
        ->where(function ($q) {
            $q->where('refund_status', '!=', 'refunded')
                ->orWhereNull('refund_status')->where('order_type', '=', 'pos');
        });

        $total_pos_order_earnings = $pos_earningsQuery->sum('order_amount');

        $total_refunds = (clone $baseOrderQuery)
            ->where('refund_status', 'refunded')
            ->sum('order_amount');

        $total_withdrawals = WalletWithdrawalsTransaction::where('status', 'approved')->sum('amount');


        $total_tax = OrderDetail::whereHas('order', function ($orderQuery) use ($filters) {
            $orderQuery->where(function ($q) {
                $q->where('refund_status', '!=', 'refunded')
                    ->orWhereNull('refund_status');
            })->where('payment_status', 'paid');
        })->sum('total_tax_amount');


        $total_order_revenue = $total_order_amount_query;

        // total admin revenue calculate
        $total_with_tax_revenue =  $total_tax;
        $total_without_tax_revenue = $total_order_revenue - $total_with_tax_revenue;
        $total_revenue = $total_without_tax_revenue;

        return [
            'total_customers' => $total_customer,
            'total_products' => $total_product,
            'total_deliverymen' => $total_deliverymen,
            'total_categories' => $total_categories,
            'total_brands' => $total_brands,
            'total_coupons' => $total_coupons,
            'total_blogs' => $total_blogs,
            'total_tickets' => $total_tickets,
            'total_orders' => $total_order,
            'total_pending_orders' => $pending_orders,
            'total_confirmed_orders' => $confirmed_orders,
            'total_processing_orders' => $processing_orders,
            'total_shipped_orders' => $shipped_orders,
            'total_delivered_orders' => $completed_orders,
            'total_cancelled_orders' => $cancelled_orders,
            'deliveryman_not_assigned_orders' => $deliveryman_not_assigned_orders,
            'total_refunded_orders' => $refunded_orders,
            'total_earnings' => $total_order_amount_without_pos,
            'total_refunds' => $total_refunds,
            'total_order_revenue' => $total_order_revenue,
            'total_withdrawals' => $total_withdrawals,
            'total_tax' => $total_tax,
            'total_revenue' => $total_revenue,
            'total_pos_order_earnings' => $total_pos_order_earnings,
        ];
    }


    public function getSummaryDataWithFilters(array $filters): array
    {
        $orderQuery = Order::query();

        if (!empty($filters['order_status'])) {
            $orderQuery->where('status', $filters['order_status']);
        }

        if (!empty($filters['customer_id'])) {
            $orderQuery->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['payment_gateway'])) {
            $orderQuery->where('payment_gateway', $filters['payment_gateway']);
        }

        if (!empty($filters['payment_status'])) {
            $orderQuery->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $orderQuery->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        } elseif (!empty($filters['start_date'])) {
            $orderQuery->whereDate('created_at', '>=', $filters['start_date']);
        } elseif (!empty($filters['end_date'])) {
            $orderQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['zone_id'])) {
            $orderQuery->where('zone_id', $filters['zone_id']);
        }

        // Now calculate the rest same as before using the filtered query
        $total_order = $orderQuery->count();
        $pending_orders = (clone $orderQuery)->where('status', 'pending')->count();
        $confirmed_orders = (clone $orderQuery)->where('status', 'confirmed')->count();
        $processing_orders = (clone $orderQuery)->where('status', 'processing')->count();
        $shipped_orders = (clone $orderQuery)->where('status', 'shipped')->count();
        $completed_orders = (clone $orderQuery)->where('status', 'delivered')->count();
        $cancelled_orders = (clone $orderQuery)->where('status', 'cancelled')->count();
        $deliveryman_not_assigned_orders = (clone $orderQuery)->where('status', 'processing')->whereNull('confirmed_by')->count();
        $refunded_orders = (clone $orderQuery)->where('refund_status', 'refunded')->count();

        // earnings, revenue etc
        $total_earnings = (clone $orderQuery)
            ->where('payment_status', 'paid')
            ->where(function ($q) {
                $q->where('refund_status', '!=', 'refunded')
                    ->orWhereNull('refund_status');
            })->sum('order_amount');

        $total_refunds = (clone $orderQuery)->where('refund_status', 'refunded')->sum('order_amount');
        $total_order_revenue = $total_earnings - $total_refunds;

        $total_tax = OrderDetail::whereHas('order', function ($q) use ($filters) {
            $q->where(function ($subQ) {
                $subQ->where('refund_status', '!=', 'refunded')->orWhereNull('refund_status');
            })->where('payment_status', 'paid');
        })->sum('total_tax_amount');


        $total_revenue = $total_order_revenue - $total_tax;

        // static counts
        $total_branches = Branch::count();
        $total_product = Product::count();
        $total_customer = Customer::count();
        $total_areas = Zone::count();
        $total_deliverymen = User::where('activity_scope', 'delivery_level')->count();
        $total_categories = ProductCategory::count();
        $total_brands = ProductBrand::count();
        $total_coupons = Coupon::count();
        $total_pages = Page::count();
        $total_blogs = Blog::count();
        $total_tickets = Ticket::count();
        $total_withdrawals = WalletWithdrawalsTransaction::where('status', 'approved')->sum('amount');

        return [
            'total_customers' => $total_customer,
            'total_branches' => $total_branches,
            'total_products' => $total_product,
            'total_deliverymen' => $total_deliverymen,
            'total_areas' => $total_areas,
            'total_categories' => $total_categories,
            'total_brands' => $total_brands,
            'total_coupons' => $total_coupons,
            'total_pages' => $total_pages,
            'total_blogs' => $total_blogs,
            'total_tickets' => $total_tickets,
            'total_orders' => $total_order,
            'total_pending_orders' => $pending_orders,
            'total_confirmed_orders' => $confirmed_orders,
            'total_processing_orders' => $processing_orders,
            'total_shipped_orders' => $shipped_orders,
            'total_delivered_orders' => $completed_orders,
            'total_cancelled_orders' => $cancelled_orders,
            'deliveryman_not_assigned_orders' => $deliveryman_not_assigned_orders,
            'total_refunded_orders' => $refunded_orders,
            'total_earnings' => $total_earnings,
            'total_refunds' => $total_refunds,
            'total_order_revenue' => $total_order_revenue,
            'total_withdrawals' => $total_withdrawals,
            'total_tax' => $total_tax,
            'total_revenue' => $total_revenue,
        ];
    }


    public function getSalesSummaryData(array $filters)
    {
        $query = Order::query();

        if (!empty($filters['this_week'])) {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif (!empty($filters['this_month'])) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif (!empty($filters['this_year'])) {
            $startDate = Carbon::now()->startOfYear();
            $endDate = Carbon::now()->endOfYear();
        } elseif (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
        }

        if (isset($startDate) && isset($endDate)) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query
            ->where('status', 'delivered')
            ->selectRaw('DATE(created_at) as date, SUM(order_amount) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getOrderGrowthData(array $filters = [])
    {
        $year = Carbon::now()->year;
        $query = Order::query();
        // Fetch order counts per month
        $monthlyData = $query
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->pluck('total_orders', 'month');

        // Fill missing months with 0
        return collect(range(1, 12))->mapWithKeys(fn($month) => [$month => $monthlyData->get($month, 0)]);
    }

    public function getOtherSummaryData(array $filters = [])
    {
        $topRatedProducts = $this->getTopRatedProducts($filters);
        $topSellingBranches = $this->getTopSellingStores($filters);
        $recentCompletedOrders = $this->getRecentCompletedOrders($filters);
        $topCategories = $this->getTopCategories($filters);

        return [
            'top_rated_products' => $topRatedProducts,
            'top_selling_branches' => $topSellingBranches,
            'recent_completed_orders' => $recentCompletedOrders,
            'top_categories' => $topCategories,
        ];
    }

    public function getTopCategories(array $filters = [])
    {
        $productQuery = Product::query()
            ->select('category_id')
            ->whereNotNull('category_id');

        if (!empty($filters['store_type'])) {
            $productQuery->whereHas('store', function ($query) use ($filters) {
                $query->where('type', $filters['store_type']);
            });
        }

        $topCategoryIds = $productQuery
            ->groupBy('category_id')
            ->orderByRaw('SUM(order_count) DESC')
            ->limit(10)
            ->pluck('category_id');

        return ProductCategory::with('translations')
            ->whereIn('id', $topCategoryIds)
            ->get()
            ->sortBy(function ($category) use ($topCategoryIds) {
                return array_search($category->id, $topCategoryIds->toArray());
            })
            ->values();
    }

    public function getTopRatedProducts($filters = [])
    {
        $query = Product::query();

        if (!empty($filters['product_type'])) {
            $query->where('type', $filters['product_type']);
        }

        return $query
            ->with(['variants'])
            ->where('products.status', 'approved')
            ->whereNull('products.deleted_at')
            ->leftJoin('reviews', function ($join) {
                $join->on('products.id', '=', 'reviews.reviewable_id')
                    ->where('reviews.reviewable_type', '=', Product::class)
                    ->where('reviews.status', '=', 'approved');
            })
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                'products.image',
                'products.status',
                DB::raw('COALESCE(AVG(reviews.rating), 0) as avg_rating')
            ])
            ->groupBy([
                'products.id',
                'products.name',
                'products.slug',
                'products.image',
                'products.status'
            ])
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->get();
    }

    public function getTopSellingStores($filters = [])
    {
        $query = Order::query();

        return $query
            ->with('branch')
            ->where('status', 'delivered')
            ->selectRaw('branch_id, SUM(order_amount) as total_sales')
            ->groupBy('branch_id')
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();
    }

    public function getRecentCompletedOrders($filters = [])
    {
        $query = Order::query();

        return $query
            ->with(['customer', 'orderDetail','deliveryman', 'shippingAddress'])
            ->where('status', 'delivered')
            ->orderByDesc('delivery_completed_at')
            ->limit(5)
            ->get();
    }
}
