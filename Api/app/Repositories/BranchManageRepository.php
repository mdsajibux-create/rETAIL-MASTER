<?php

namespace App\Repositories;

use App\Http\Resources\BranchDetailsResource;
use App\Interfaces\BranchManageInterface;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Branch\app\Models\Branch;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductStock;

class BranchManageRepository implements BranchManageInterface
{
    public function __construct(protected Branch $store, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->store->translationKeys;
    }

    public function model(): string
    {
        return Branch::class;
    }

    public function branchList(int|string $limit, int|string|null $status,int $page,string $language,string $search,string $sortField, string $sort,array$filters)
    {
        $branches = Branch::query()
            ->leftJoin('translations as name_translations', function ($join) use ($language) {
                $join->on('branches.id', '=', 'name_translations.translatable_id')
                    ->where('name_translations.translatable_type', '=', Branch::class)
                    ->where('name_translations.language', '=', $language)
                    ->where('name_translations.key', '=', 'name');
            })
            ->select(
                'branches.*',
                DB::raw('COALESCE(name_translations.value, branches.name) as name')
            );

        //  Filter by search
        if (!empty($search)) {
            $branches->where(function ($query) use ($search) {
                $query->where(DB::raw("COALESCE(name_translations.value, branches.name)"), 'like', "%{$search}%");
            });
        }

        // status filter
        if (is_numeric($status)) {
            $branches->where('branches.status', (int)$status);
        }

        return $branches->with([
            'zone',
            'state',
            'city',
            'area',
            'related_translations'
        ])
            ->orderBy($sortField ?: 'branches.created_at', $sort ?: 'asc')
            ->paginate($limit);
    }



    public function store(array $data)
    {
        $data['created_by'] = auth('api')->id();

        try {
            $data = Arr::except($data, ['translations']);

            // if new branch set as main
            if (!empty($data['is_main']) && $data['is_main']) {
                Branch::where('is_main', true)->update(['is_main' => false]);
            }

            $branch = Branch::create($data);

            return $branch->id;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getBranchById(int|string $id)
    {
        try {
            $branch = Branch::with(['related_translations','zone', 'state', 'city','area'])->find($id);

            if ($branch) {
                return response()->json(new BranchDetailsResource($branch));
            } else {
                return response()->json([
                    'message' => __('messages.data_not_found')
                ]);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        $data['updated_by'] = auth('api')->id();

        try {
            $branch = Branch::findOrFail($data['id']);

            // if this branch is set as main
            if (!empty($data['is_main']) && $data['is_main']) {
                Branch::where('id', '!=', $branch->id)
                    ->where('is_main', true)
                    ->update(['is_main' => false]);
            }

            $branch->update(Arr::except($data, ['translations']));

            return $branch->id;

        } catch (\Throwable $th) {

        }
    }


    public function delete(int|string $id): bool
    {
        try {
            $branch = Branch::findOrFail($id);
            $this->deleteTranslation($branch->id, Branch::class);
            $branch_id = $branch->id;

            $branch->delete();

        } catch (\Throwable $th) {
        }

        return true;
    }


    private function deleteTranslation(int|string $id, string $translatableType): bool
    {
        Translation::where('translatable_id', $id)
            ->where('translatable_type', $translatableType)
            ->delete();

        return true;
    }

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }
                    // Collect translation data
                    $translations[] = [
                        'translatable_type' => $refPath,
                        'translatable_id' => $refid,
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }

                    $trans = $this->translation->where('translatable_type', $refPath)->where('translatable_id', $refid)
                        ->where('language', $translation['language_code'])->where('key', $key)->first();
                    if ($trans != null) {
                        $trans->value = $translatedValue;
                        $trans->save();
                    } else {
                        $translations[] = [
                            'translatable_type' => $refPath,
                            'translatable_id' => $refid,
                            'language' => $translation['language_code'],
                            'key' => $key,
                            'value' => $translatedValue,
                        ];
                    }
                }
            }
        }
        if (count($translations)) {
            $this->translation->insert($translations);
        }
        return true;
    }

    public function records(bool $onlyDeleted = false)
    {
        try {
            switch ($onlyDeleted) {
                case true:
                    $records = Branch::onlyTrashed()->get();
                    break;

                default:
                    $records = Branch::withTrashed()->get();
                    break;
            }
            return $records;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function getSummaryData(?string $slug = null, ?int $seller_id = null)
    {

        $branch_id = auth('api')->user()->branch_id;
        $baseQuery = Order::query();
        $summary['total_order']            = (clone $baseQuery)->count();
        $summary['confirmed_orders']       = (clone $baseQuery)->where('status', 'confirmed')->count();
        $summary['pending_orders']         = (clone $baseQuery)->where('status', 'pending')->count();
        $summary['processing_orders']      = (clone $baseQuery)->where('status', 'processing')->count();
        $summary['shipped_orders']         = (clone $baseQuery)->where('status', 'shipped')->count();
        $summary['completed_orders']       = (clone $baseQuery)->where('status', 'delivered')->count();
        $summary['cancelled_orders']       = (clone $baseQuery)->where('status', 'cancelled')->count();

        $summary['total_pos_order_amount'] = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->whereNull('refund_status')
            ->where('order_type', 'pos')
            ->sum('order_amount');

        $summary['total_order_amount'] = (clone $baseQuery)
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) {
                $q->whereNull('refund_status')
                    ->orWhere('refund_status', '!=', 'refunded');
            })
            ->where('order_type', 'regular')
            ->sum('order_amount');

        $summary['total_refunds'] = (clone $baseQuery)
            ->where('refund_status', 'refunded')
            ->sum('order_amount');

        $summary['total_branches'] = Branch::count();
        $summary['total_product']  = ProductStock::where('branch_id', $branch_id)->distinct()->count('product_id');
        $summary['total_stuff']    = User::where('activity_scope', 'branch_level')->where('branch_id', $branch_id)->count();

        $summary['deliveryman_not_assigned_orders'] = (clone $baseQuery)
            ->where('status', 'processing')
            ->whereNull('confirmed_by')
            ->count();

        $summary['refunded_orders'] = (clone $baseQuery)->where('refund_status', 'refunded')->count();
        $summary['pos_orders']      = (clone $baseQuery)->where('order_type', 'pos')->count();

        return $summary;
    }

    public function getSalesSummaryData(array $filters, ?string $slug = null)
    {
        $branch_id = auth('api')->user()->branch_id;
        $query = Order::where('branch_id', $branch_id);

        // Handle time period filter
        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        if (!empty($filters['time_period'])) {
            switch ($filters['time_period']) {
                case 'this_week':
                    $startDate = Carbon::now()->startOfWeek();
                    $endDate = Carbon::now()->endOfWeek();
                    break;
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    break;
            }
        }

        // Apply date range filter if valid
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($query->get()->isEmpty()) {
            return [
                [
                    "date" => "",
                    "total_sales" => "",
                ]
            ];
        }
        // Return grouped sales summary for delivered orders
        return $query->where('status', 'delivered')
            ->selectRaw('DATE(created_at) as date, SUM(order_amount) as total_sales')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function getOrderGrowthData(?string $slug = null)
    {
        $branch_id = auth('api')->user()->branch_id;
        $year = Carbon::now()->year;

        // Fetch order counts per month
        $monthlyData = Order::where('branch_id', $branch_id)
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->pluck('total_orders', 'month');

        return collect(range(1, 12))->mapWithKeys(fn($month) => [$month => $monthlyData->get($month, 0)]);
    }

    public function getOtherSummaryData(?string $slug = null)
    {
        $topRatedProducts = $this->getTopRatedProducts($slug);
        $recentCompletedOrders = $this->getRecentCompletedOrders($slug);

        return [
            'top_rated_products' => $topRatedProducts,
            'recent_completed_orders' => $recentCompletedOrders,
        ];
    }

    public function getTopRatedProducts($slug = null)
    {

        return Product::with(['variants'])
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->leftJoin('reviews', function ($join) {
                $join->on('products.id', '=', 'reviews.reviewable_id')
                    ->where('reviews.reviewable_type', '=', Product::class)
                    ->where('reviews.status', '=', 'active');
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

    public function getRecentCompletedOrders($slug = null)
    {
        $branch_id = auth('api')->user()->branch_id;

        return Order::with([
            'customer',
            'orderDetail',
            'deliveryman',
            'shippingAddress'
        ])
            ->where('branch_id', $branch_id)
            ->where('status', 'delivered')
            ->orderByDesc('delivery_completed_at')
            ->limit(5)
            ->get();
    }


    public function changeStatus(array $data)
    {
        try {
            $store = Branch::where('id', $data['id'])
                ->where('deleted_at', null)
                ->update([
                    'status' => $data['status']
                ]);
            return $store->count() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
