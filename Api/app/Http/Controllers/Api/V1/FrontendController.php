<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Behaviour;
use App\Http\Resources\BranchDetailsPublicResource;
use App\Http\Resources\Com\BehaviourPublicResource;
use App\Http\Resources\Com\BranchPublicDropdownResource;
use App\Http\Resources\Com\ComZoneListForDropdownResource;
use App\Http\Resources\Com\ContactUsPublicResource;
use App\Http\Resources\Com\DepartmentListForDropdown;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Com\PrivacyPolicyResource;
use App\Http\Resources\Com\ProductAttributeResource;
use App\Http\Resources\Com\ProductBrandPublicResource;
use App\Http\Resources\Com\ProductCategoryPublicResource;
use App\Http\Resources\Com\ProductCategoryResource;
use App\Http\Resources\Com\ProductUnitPublicResource;
use App\Http\Resources\CouponPublicResource;
use App\Http\Resources\Customer\CustomerPublicResource;
use App\Http\Resources\PageDetailsPublicResource;
use App\Http\Resources\PageListResource;
use App\Http\Resources\TagPublicResource;
use App\Interfaces\OrderRefundInterface;
use App\Interfaces\ProductManageInterface;
use App\Models\Customer;
use App\Models\Department;
use App\Models\SystemCharge;
use App\Models\Translation;
use App\Services\FlashSaleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Branch\app\Models\Branch;
use Modules\BusinessSettings\app\Models\ProductType;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\BusinessSettings\app\Transformers\StoreTypeDropdownPublicResource;
use Modules\Catalog\app\Models\ProductAttribute;
use Modules\Catalog\app\Models\ProductBrand;
use Modules\Catalog\app\Models\ProductCategory;
use Modules\Catalog\app\Models\Tag;
use Modules\Catalog\app\Models\Unit;
use Modules\Coupon\app\Models\CouponLine;
use Modules\Order\app\Transformers\OrderRefundReasonResource;
use Modules\Product\app\Models\FlashSaleProduct;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductStock;
use Modules\Product\app\Models\ProductView;
use Modules\Product\app\Transformers\FlashSaleAllProductPublicResource;
use Modules\Product\app\Transformers\FlashSaleWithProductPublicResource;
use Modules\Product\app\Transformers\NewArrivalPublicHomeResource;
use Modules\Product\app\Transformers\ProductDetailsPublicResource;
use Modules\Product\app\Transformers\ProductDetailsPublicWebResource;
use Modules\Product\app\Transformers\ProductKeywordSuggestionPublicResource;
use Modules\Product\app\Transformers\ProductPublicHomeResource;
use Modules\Product\app\Transformers\ProductPublicResource;
use Modules\Product\app\Transformers\ProductSuggestionPublicResource;
use Modules\Product\app\Transformers\RelatedProductPublicResource;
use Modules\SystemCore\app\Models\Page;

class FrontendController extends Controller
{
    public function __construct(
        protected ProductManageInterface $productRepo,
        protected FlashSaleService       $flashSaleService,
        protected OrderRefundInterface   $orderRefundRepo
    )
    {

    }

    public function departments()
    {
        $departments = Department::where('status', 1)->get();
        if ($departments->isNotEmpty()) {
            return response()->json([
                    'status' => true,
                    'status_code' => 200,
                    'data' => DepartmentListForDropdown::collection($departments)
                ]
            );
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found')
            ]);
        }
    }

    public function branchDetails(Request $request)
    {

        $branch = Branch::with(['zone', 'related_translations'])
            ->where('slug', $request->slug)
            ->first();

        if (!$branch && empty($branch)) {
            return response()->json([
                'message' => __('messages.data_not_found')
            ], 404);
        }

        return response()->json([
            'status' => true,
            'status_code' => 200,
            'message' => __('messages.data_found'),
            'data' => new BranchDetailsPublicResource($branch),
        ]);
    }

    public function keywordSuggestions(Request $request)
    {
        // Validate the query input
        $query = $request->input('query');
        if (!$query) {
            return response()->json([
                'message' => 'Query parameter is required.',
            ], 422);
        }

        // Search dynamically based on product title or description
        $keywords = Product::query()
            ->select('id', 'name', 'slug', 'image','description', 'status')
            ->where('status', 'active')
            ->where(function ($q) use ($query) {

                // ── Split query into individual words ─────────
                $words = explode(' ', trim($query));

                foreach ($words as $word) {
                    if (strlen($word) < 2) continue; // skip single chars

                    $q->orWhere('name', 'like', "%{$word}%")
                        ->orWhere('description', 'like', "%{$word}%")
                        ->orWhereHas('tags', fn($t) => $t->where('name', 'like', "%{$word}%"))
                        ->orWhereHas('variants', fn($v) => $v->where('sku', 'like', "%{$word}%"));
                }
            })
            ->limit(10)
            ->get();


        return response()->json([
            'success' => true,
            'data' => ProductKeywordSuggestionPublicResource::collection($keywords),
        ]);
    }

    public function searchSuggestions(Request $request)
    {
        // Validate the query input
        $query = $request->input('query');
        if (!$query) {
            return response()->json([
                'message' => 'Query parameter is required.',
            ], 200);
        }

        $branchId = isWebBranch(); // must return the web branch id

        $productSuggestions = Product::query()
            ->select('products.*')
            ->where('type', getThemeProductType())
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            // only products available in web branch
            ->whereHas('stocks', function ($stockQuery) use ($branchId) {
                $stockQuery->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            })
            ->where(function ($productQuery) use ($query) {
                $productQuery->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($query) {
                        $tagQuery->where('name', 'like', "%{$query}%");
                    })
                    ->orWhereHas('variants', function ($variantQuery) use ($query) {
                        $variantQuery->where('sku', 'like', "%{$query}%")
                            ->orWhere('attributes', 'like', "%{$query}%");
                    });
            })
            ->with([
                'related_translations',
                'category',
                'variants' => function ($variantQuery) use ($branchId) {
                    $variantQuery->select([
                        'id',
                        'product_id',
                        'variant_slug',
                        'sku',
                        'price',
                        'special_price',
                        'attributes',
                        'image',
                        'order_count',
                        'status',
                    ])
                        ->with(['stocks' => function ($stockQuery) use ($branchId) {
                            $stockQuery->where('branch_id', $branchId)
                                ->where('qty', '>', 0);
                        }]);
                },
            ])
            ->get();

        return response()->json([
            'data' => ProductSuggestionPublicResource::collection($productSuggestions),
        ], 200);
    }


    public function popularProducts(Request $request)
    {
        $branchId = isWebBranch();

        if (isset($request->id)) {
                $product = Product::with([
                    'variants.stocks' => function ($q) use ($branchId) {
                        $q->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    },
                    'related_translations'
                ])
                ->where('type', getThemeProductType())
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->findOrFail($request->id);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new ProductDetailsPublicResource($product),
                'related_products' => RelatedProductPublicResource::collection(
                    $product->relatedProductsWithCategoryFallback()
                )
            ]);
        }

        $query = Product::query()
            ->where('products.type', getThemeProductType())
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->whereHas('stocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            })
            ->with([
                'stocks' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                }
            ]);


        // Location wise product filter
        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $useLocationFilter = false;

        if ($userLat && $userLng) {
            $radius = $request->radius ?? 10;

            // Clone the base query to apply location filter
            $locationQuery = clone $query;

            $locationQuery->select('products.*')
                ->join('product_stocks', function ($join) use ($branchId) {
                    $join->on('product_stocks.product_id', '=', 'products.id')
                        ->where('product_stocks.branch_id', $branchId)
                        ->where('product_stocks.qty', '>', 0);
                })
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            )) AS distance
        ', [$userLat, $userLng, $userLat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');

            // Test if location-filtered query returns any product
            $testResults = (clone $locationQuery)->take(1)->get();

            if ($testResults->isNotEmpty()) {
                $query = $locationQuery;
                $useLocationFilter = true;
            }
        }

        // Category filter (including child categories)
        if (!empty($request->category_id) && is_array($request->category_id)) {
            $allCategoryIds = [];

            foreach ($request->category_id as $categoryId) {
                $category = ProductCategory::find($categoryId);
                if ($category) {
                    if ($category->parent_id === null) {
                        $childIds = ProductCategory::where('parent_id', $category->id)->pluck('id')->toArray();
                        $allCategoryIds = array_merge($allCategoryIds, $childIds);
                    }
                    $allCategoryIds[] = $category->id;
                }
            }

            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            }
        }

        // Brand filter
        if (!empty($request->brand_id) && is_array($request->brand_id)) {
            $query->whereIn('brand_id', $request->brand_id);
        }
        // Price range filter
        if (isset($request->min_price) && isset($request->max_price)) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->whereBetween('price', [$request->min_price, $request->max_price]);
            });
        }
        // Availability filter
        if (isset($request->availability)) {
            $query->whereHas('variants', fn($q) => $q->where('stock_quantity', $request->availability ? '>' : '=', 0)
            );
        }
        // Type filter
        if (!empty($request->type)) {
            if (is_array($request->type)) {
                $query->whereIn('type', $request->type);
            } else {
                $query->where('type', $request->type);
            }
        }
        // Minimum rating filter
        if (isset($request->min_rating)) {
            $avgRatingSub = DB::table('reviews')
                ->select('reviewable_id', DB::raw('AVG(rating) as average_rating'))
                ->where('reviewable_type', Product::class)
                ->where('status', 'active')
                ->groupBy('reviewable_id');

            $query->joinSub($avgRatingSub, 'product_ratings', function ($join) {
                $join->on('products.id', '=', 'product_ratings.reviewable_id');
            })->where('product_ratings.average_rating', '>=', $request->min_rating);
        }

        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price_low_high':
                case 'price_high_low':
                    $aggregateFunction = $request->sort === 'price_low_high' ? 'MIN' : 'MAX';

                    $query->addSelect([
                        'effective_price' => \DB::table('product_variants')
                            ->selectRaw("{$aggregateFunction}(CASE 
                        WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                            THEN special_price 
                        ELSE price 
                    END)")
                            ->whereColumn('product_variants.product_id', 'products.id')
                    ])->orderBy('effective_price', $request->sort === 'price_low_high' ? 'asc' : 'desc');
                    break;

                case 'newest':
                    $query->orderBy('products.created_at', 'desc');
                    break;

                default:
                    $query->latest('products.created_at');
            }
        }

        // Search filter
        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Featured products
        if (isset($request->is_featured) && $request->is_featured) {
            $query->where('is_featured', true);
        }

        // Base filters
        $query->where('products.status', 'active')->whereNull('products.deleted_at');

        // Order by most viewed
        $query->orderByDesc('views');

        // Pagination
        $perPage = $request->per_page ?? 10;

        $products = $query->with([
            'category', 'unit', 'tags', 'brand', 'related_translations',
            'variants' => function ($query) use ($request) {
                $query->select('*')
                    ->addSelect(DB::raw('
            CASE 
                WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                    THEN special_price 
                ELSE price 
            END as effective_price
        '));

                if ($request->sort === "price_low_high") {
                    $query->orderBy('effective_price', 'asc')->limit(1);
                } elseif ($request->sort === "price_high_low") {
                    $query->orderBy('effective_price', 'desc')->limit(1);
                }
            }
        ])->paginate($perPage);

        $uniqueAttributes = $this->getUniqueAttributesFromVariants($products);

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => ProductPublicResource::collection($products),
            'meta' => new PaginationResource($products),
            'filters' => $uniqueAttributes,
            'locationFilter' => $useLocationFilter
        ], 200);
    }

    public function bestSellingProducts(Request $request)
    {

        $branchId = isWebBranch();

        // If product ID is passed, return a single product with details
        if (isset($request->id)) {
            $product = Product::with([
                'variants.stocks' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                },
                'related_translations'
            ])
                ->where('type', getThemeProductType())
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->findOrFail($request->id);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new ProductDetailsPublicResource($product),
                'related_products' => RelatedProductPublicResource::collection(
                    $product->relatedProductsWithCategoryFallback()
                )
            ]);
        }


        $query = Product::query()
            ->where('products.type', getThemeProductType())
            ->where('products.status', 'active')
            ->whereNull('products.deleted_at')
            ->whereHas('stocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            })
            ->with([
                'stocks' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                }
            ]);

        // Location wise product filter
        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $useLocationFilter = false;

        if ($userLat && $userLng) {
            $radius = $request->radius ?? 10;

            // Clone the base query to apply location filter
            $locationQuery = clone $query;

            $locationQuery->select('products.*')
                ->join('product_stocks', function ($join) use ($branchId) {
                    $join->on('product_stocks.product_id', '=', 'products.id')
                        ->where('product_stocks.branch_id', $branchId)
                        ->where('product_stocks.qty', '>', 0);
                })
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            )) AS distance
        ', [$userLat, $userLng, $userLat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');

            // Test if location-filtered query returns any product
            $testResults = (clone $locationQuery)->take(1)->get();

            if ($testResults->isNotEmpty()) {
                $query = $locationQuery;
                $useLocationFilter = true;
            }
        }

        // Category filter (including child categories)
        if (!empty($request->category_id) && is_array($request->category_id)) {
            $allCategoryIds = [];

            foreach ($request->category_id as $categoryId) {
                $category = ProductCategory::find($categoryId);
                if ($category) {
                    if ($category->parent_id === null) {
                        $childIds = ProductCategory::where('parent_id', $category->id)->pluck('id')->toArray();
                        $allCategoryIds = array_merge($allCategoryIds, $childIds);
                    }
                    $allCategoryIds[] = $category->id;
                }
            }

            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            }
        }

        // Brand filter
        if (!empty($request->brand_id) && is_array($request->brand_id)) {
            $query->whereIn('brand_id', $request->brand_id);
        }

        // Price range filter
        if (isset($request->min_price) && isset($request->max_price)) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->whereBetween('price', [$request->min_price, $request->max_price]);
            });
        }

        // Availability filter
        if (isset($request->availability)) {
            $query->whereHas('variants', fn($q) => $q->where('stock_quantity', $request->availability ? '>' : '=', 0)
            );
        }

        // Type filter
        if (!empty($request->type)) {
            if (is_array($request->type)) {
                $query->whereIn('type', $request->type);
            } else {
                $query->where('type', $request->type);
            }
        }

        // Minimum rating filter
        if (isset($request->min_rating)) {
            $avgRatingSub = DB::table('reviews')
                ->select('reviewable_id', DB::raw('AVG(rating) as average_rating'))
                ->where('reviewable_type', Product::class)
                ->where('status', 'active')
                ->groupBy('reviewable_id');

            $query->joinSub($avgRatingSub, 'product_ratings', function ($join) {
                $join->on('products.id', '=', 'product_ratings.reviewable_id');
            })->where('product_ratings.average_rating', '>=', $request->min_rating);
        }

        // Sorting logic
        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price_low_high':
                case 'price_high_low':
                    $aggregateFunction = $request->sort === 'price_low_high' ? 'MIN' : 'MAX';

                    $query->addSelect([
                        'effective_price' => \DB::table('product_variants')
                            ->selectRaw("{$aggregateFunction}(CASE 
                        WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                            THEN special_price 
                        ELSE price 
                    END)")
                            ->whereColumn('product_variants.product_id', 'products.id')
                    ])->orderBy('effective_price', $request->sort === 'price_low_high' ? 'asc' : 'desc');
                    break;

                case 'newest':
                    $query->orderBy('products.created_at', 'desc');
                    break;

                default:
                    $query->latest('products.created_at');
            }
        }

        // Search filter
        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Featured products
        if (isset($request->is_featured) && $request->is_featured) {
            $query->where('is_featured', true);
        }

        $query->where('products.status', 'active')->whereNull('products.deleted_at');

        // Order by best-selling (order_count)
        $query->orderByDesc('order_count');

        // Pagination
        $perPage = $request->per_page ?? 10;

        $products = $query->with([
            'category', 'unit', 'tags', 'brand', 'related_translations',
            'variants' => function ($query) use ($request) {
                $query->select('*')
                    ->addSelect(DB::raw('
            CASE 
                WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                    THEN special_price 
                ELSE price 
            END as effective_price
        '));

                if ($request->sort === "price_low_high") {
                    $query->orderBy('effective_price', 'asc')->limit(1);
                } elseif ($request->sort === "price_high_low") {
                    $query->orderBy('effective_price', 'desc')->limit(1);
                }
            }
        ])->paginate($perPage);

        $uniqueAttributes = $this->getUniqueAttributesFromVariants($products);

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => ProductPublicResource::collection($products),
            'meta' => new PaginationResource($products),
            'filters' => $uniqueAttributes,
            'locationFilter' => $useLocationFilter
        ], 200);
    }


    public function flashDealProducts(Request $request)
    {
        $branchId = isWebBranch();

        // If a specific flash deal product ID is requested
        if (isset($request->id)) {
            $flashDealProduct = FlashSaleProduct::with([
                'product.related_translations',
                'flashSale.related_translations',
                'product.variants' => function ($q) use ($branchId) {
                    $q->with(['stocks' => function ($sq) use ($branchId) {
                        $sq->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    }]);
                },
                'product.stocks' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                },
            ])->where('product_id', $request->product_id)
                ->first();

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new FlashSaleAllProductPublicResource($flashDealProduct)
            ]);
        }

        $query = FlashSaleProduct::query()->with([
            'product.category',
            'product.unit',
            'product.tags',
            'product.brand',
            'product.related_translations',
            'flashSale.related_translations',
            'product.stocks' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            },
            'product.variants' => function ($q) use ($branchId) {
                $q->with(['stocks' => function ($sq) use ($branchId) {
                    $sq->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                }]);
            },
        ])->whereHas('flashSale', function ($query) {
            $query->where('status', 1);
        })->whereHas('product', function ($q) use ($branchId) {
                $q->where('products.type', getThemeProductType())
                    ->where('products.status','active')
                    ->whereNull('products.deleted_at')
                    ->whereHas('stocks', function ($sq) use ($branchId) {
                        $sq->where('branch_id',$branchId)
                            ->where('qty','>',0);
                    });
            });



        // Location wise product filter
        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $useLocationFilter = false;

        if ($userLat && $userLng) {
            $radius = $request->radius ?? 10;

            // Base query backup
            $baseQuery = clone $query;

            // Build location-aware flash sale product filter
            $locationQuery = $query
                ->join('products', 'products.id', '=', 'flash_sale_products.product_id')
                ->join('product_stocks', function ($join) use ($branchId) {
                    $join->on('product_stocks.product_id','=','products.id')
                        ->where('product_stocks.branch_id',$branchId)
                        ->where('product_stocks.qty','>',0);
                })
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->select('flash_sale_products.*')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            )) AS distance
        ', [$userLat, $userLng, $userLat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');

            if ($locationQuery->take(1)->exists()) {
                $query = $locationQuery;
                $useLocationFilter = true;
            } else {
                $query = $baseQuery;
                $useLocationFilter = false;
            }
        }


        // Apply category filter (multiple categories)
        if (!empty($request->category_id) && is_array($request->category_id)) {
            // Fetch all child categories for the given category IDs
            $allCategoryIds = [];

            foreach ($request->category_id as $categoryId) {
                // Check if the category is a parent category
                $category = ProductCategory::where('id', $categoryId)->first();

                if ($category) {
                    if ($category->parent_id === null) {
                        // Fetch all child category IDs of this parent category
                        $childCategoryIds = ProductCategory::where('parent_id', $category->id)->pluck('id')->toArray();
                        $allCategoryIds = array_merge($allCategoryIds, $childCategoryIds);
                    }

                    // Add the original category ID
                    $allCategoryIds[] = $category->id;
                }
            }

            // Apply the category filter
            $query->whereHas('product', function ($q1) use ($allCategoryIds) {
                $q1->whereIn('category_id', $allCategoryIds);
            });
        }

        if (!empty($request->type)) {
            if (is_array($request->type)) {
                $query->whereHas('product', function ($q1) use ($request) {
                    $q1->whereIn('type', $request->type);
                });
            } else {
                $query->whereHas('product', function ($q1) use ($request) {
                    $q1->where('type', $request->type);
                });
            }
        }

        // Store filter
        if (!empty($request->store_id)) {
            $query->where('store_id', $request->store_id);
        }

        // Flash Sale ID filter
        if (filter_var($request->flash_sale_id, FILTER_VALIDATE_INT) && (int)$request->flash_sale_id !== 0) {
            $query->where('flash_sale_id', (int)$request->flash_sale_id);
        }

        // Status filter
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        // Search by product name or SKU
        if (!empty($request->search)) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('sku', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by minimum rating
        if (isset($request->min_rating)) {
            $avgRatingSub = DB::table('reviews')
                ->select('reviewable_id', DB::raw('AVG(rating) as average_rating'))
                ->where('reviewable_type', Product::class)
                ->where('status', 'active')
                ->groupBy('reviewable_id');

            $query->whereHas('product', function ($q) use ($avgRatingSub, $request) {
                $q->joinSub($avgRatingSub, 'product_ratings', function ($join) {
                    $join->on('products.id', '=', 'product_ratings.reviewable_id');
                })->where('product_ratings.average_rating', '>=', $request->min_rating);
            });
        }

        // Sort options
        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price_low_high':
                case 'price_high_low':
                    $aggregateFunction = $request->sort === 'price_low_high' ? 'MIN' : 'MAX';

                    $query->addSelect([
                        'effective_price' => \DB::table('product_variants')
                            ->selectRaw("{$aggregateFunction}(CASE 
                        WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                            THEN special_price 
                        ELSE price 
                    END)")
                            ->whereColumn('product_variants.product_id', 'products.id')
                    ])->orderBy('effective_price', $request->sort === 'price_low_high' ? 'asc' : 'desc');
                    break;

                case 'newest':
                    $query->orderBy('products.created_at', 'desc');
                    break;

                default:
                    $query->latest('products.created_at');
            }
        }

        // Pagination
        $perPage = $request->per_page ?? 10;
        $flashSaleProducts = $query->paginate($perPage);

        // Collect unique attributes from the variants (if needed)
        $uniqueAttributes = $this->getUniqueAttributesFromVariants($flashSaleProducts->pluck('product'));

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => FlashSaleAllProductPublicResource::collection($flashSaleProducts),
            'meta' => new PaginationResource($flashSaleProducts),
            'filters' => $uniqueAttributes,
            'locationFilter' => $useLocationFilter
        ], 200);
    }


    public function featuredProducts(Request $request)
    {

        $branchId = isWebBranch();

        // If product ID is passed, return a single featured product with details
        if (isset($request->id)) {
            $product = Product::with([
                'stocks' => function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                },
                'variants' => function ($q) use ($branchId) {
                    $q->with(['stocks' => function ($sq) use ($branchId) {
                        $sq->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    }]);
                },
                'related_translations'
            ])
                ->where('type', getThemeProductType())
                ->where('status', 'active')
                ->whereNull('deleted_at')
                ->where('is_featured', true)
                ->findOrFail($request->id);

            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => new ProductDetailsPublicResource($product),
                'related_products' => RelatedProductPublicResource::collection(
                    $product->relatedProductsWithCategoryFallback()
                )
            ]);
        }



        $query = Product::query()
            ->where('products.type', getThemeProductType())
            ->where('products.status', 'active')
            ->where('products.is_featured', 1)
            ->whereNull('products.deleted_at')
            ->whereHas('stocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            });

        // Location wise product filter
        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $useLocationFilter = false;

        if ($userLat && $userLng) {
            $radius = $request->radius ?? 10;

            // Clone the base query to apply location filter
            $locationQuery = clone $query;

            $locationQuery->select('products.*')
                ->distinct()
                ->join('product_stocks', function ($join) use ($branchId) {
                    $join->on('product_stocks.product_id', '=', 'products.id')
                        ->where('product_stocks.branch_id', $branchId)
                        ->where('product_stocks.qty', '>', 0);
                })
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            )) AS distance
        ', [$userLat, $userLng, $userLat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');

            // Test if location-filtered query returns any product
            $testResults = (clone $locationQuery)->take(1)->get();

            if ($testResults->isNotEmpty()) {
                $query = $locationQuery;
                $useLocationFilter = true;
            }
        }

        // Category filter (including child categories)
        if (!empty($request->category_id) && is_array($request->category_id)) {
            $allCategoryIds = [];

            foreach ($request->category_id as $categoryId) {
                $category = ProductCategory::find($categoryId);
                if ($category) {
                    if ($category->parent_id === null) {
                        $childIds = ProductCategory::where('parent_id', $category->id)->pluck('id')->toArray();
                        $allCategoryIds = array_merge($allCategoryIds, $childIds);
                    }
                    $allCategoryIds[] = $category->id;
                }
            }

            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            }
        }

        // Brand filter
        if (!empty($request->brand_id) && is_array($request->brand_id)) {
            $query->whereIn('brand_id', $request->brand_id);
        }

        // Price range filter
        if (isset($request->min_price) && isset($request->max_price)) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->whereBetween('price', [$request->min_price, $request->max_price]);
            });
        }

        // Availability filter
        if (isset($request->availability)) {
            $query->whereHas('variants', fn($q) => $q->where('stock_quantity', $request->availability ? '>' : '=', 0));
        }

        // Type filter
        if (!empty($request->type)) {
            if (is_array($request->type)) {
                $query->whereIn('type', $request->type);
            } else {
                $query->where('type', $request->type);
            }
        }

        // Minimum rating filter
        if (isset($request->min_rating)) {
            $avgRatingSub = DB::table('reviews')
                ->select('reviewable_id', DB::raw('AVG(rating) as average_rating'))
                ->where('reviewable_type', Product::class)
                ->where('status', 'active')
                ->groupBy('reviewable_id');

            $query->joinSub($avgRatingSub, 'product_ratings', function ($join) {
                $join->on('products.id', '=', 'product_ratings.reviewable_id');
            })->where('product_ratings.average_rating', '>=', $request->min_rating);
        }

        // Search filter
        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sorting logic
        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price_low_high':
                case 'price_high_low':
                    $aggregateFunction = $request->sort === 'price_low_high' ? 'MIN' : 'MAX';

                    $query->addSelect([
                        'effective_price' => \DB::table('product_variants')
                            ->selectRaw("{$aggregateFunction}(CASE 
                        WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                            THEN special_price 
                        ELSE price 
                    END)")
                            ->whereColumn('product_variants.product_id', 'products.id')
                    ])->orderBy('effective_price', $request->sort === 'price_low_high' ? 'asc' : 'desc');
                    break;

                case 'newest':
                    $query->orderBy('products.created_at', 'desc');
                    break;

                default:
                    $query->latest('products.created_at');
            }
        }

        $query->where('products.status', 'active')->where('products.is_featured', true)->whereNull('products.deleted_at');

        // Pagination
        $perPage = $request->per_page ?? 10;

        $products = $query->with([
            'category', 'unit', 'tags', 'brand', 'related_translations',
            'variants' => function ($query) use ($request) {
                $query->select('*')
                    ->addSelect(DB::raw('
            CASE 
                WHEN special_price IS NOT NULL AND special_price > 0 AND special_price < price 
                    THEN special_price 
                ELSE price 
            END as effective_price
        '));

                if ($request->sort === "price_low_high") {
                    $query->orderBy('effective_price', 'asc')->limit(1);
                } elseif ($request->sort === "price_high_low") {
                    $query->orderBy('effective_price', 'desc')->limit(1);
                }
            }
        ])->latest()->paginate($perPage);

        $uniqueAttributes = $this->getUniqueAttributesFromVariants($products);

        return response()->json([
            'message' => __('messages.data_found'),
            'data' => ProductPublicResource::collection($products),
            'meta' => new PaginationResource($products),
            'filters' => $uniqueAttributes,
            'locationFilter' => $useLocationFilter
        ]);
    }

    public function products(Request $request)
    {

        if ($request->popular_products) {
            return $this->popularProducts($request);
        }
        if ($request->best_selling) {
            return $this->bestSellingProducts($request);
        }
        if ($request->flash_sale) {
            return $this->flashDealProducts($request);
        }
        if ($request->is_featured) {
            return $this->featuredProducts($request);
        }


        $branchId = isWebBranch();
        $language = $request->input('language', app()->getLocale());
        $perPage = (int) ($request->per_page ?? 20);


        $cachePayload = [
            'branch_id'   => $branchId,
            'language'    => $language,
            'per_page'    => $perPage,
            'page'        => (int) $request->input('page', 1),
            'category_id' => $request->category_id,
            'brand_id'    => $request->brand_id,
            'min_price'   => $request->min_price,
            'max_price'   => $request->max_price,
            'type'        => $request->type,
            'min_rating'  => $request->min_rating,
            'sort'        => $request->sort,
            'search'      => $request->search,
            'user_lat'    => $request->user_lat,
            'user_lng'    => $request->user_lng,
            'radius'      => $request->radius,
            'theme_type'  => getThemeProductType(),
        ];

        $cacheKey = 'products:' . md5(json_encode($cachePayload));
        $cached = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($request, $branchId, $perPage) {

        // check theme, branch wise get stock . only is_web branch wise get stock
        $query = Product::query()
            ->where('products.type', getThemeProductType())
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


        // Location wise product filter
        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $useLocationFilter = false;

        // Apply category filter (multiple categories)
        if (!empty($request->category_id) && is_array($request->category_id)) {
            // Fetch all child categories for the given category IDs
            $allCategoryIds = [];

            foreach ($request->category_id as $categoryId) {
                // Check if the category is a parent category
                $category = ProductCategory::where('id', $categoryId)->first();
                if ($category) {
                    if ($category->parent_id === null) {
                        // Fetch all child category IDs of this parent category
                        $childCategoryIds = ProductCategory::where('parent_id', $category->id)->pluck('id')->toArray();
                        $allCategoryIds = array_merge($allCategoryIds, $childCategoryIds);
                    }

                    // Add the original category ID
                    $allCategoryIds[] = $category->id;
                }
            }

            // Apply the category filter
            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', $allCategoryIds);
            }
        }

        if (!empty($request->brand_id) && is_array($request->brand_id)) {
            $query->whereIn('brand_id', $request->brand_id);
        }

        // Apply price range filter
        if (isset($request->min_price) && isset($request->max_price)) {
            $minPrice = $request->min_price;
            $maxPrice = $request->max_price;

            $query->whereHas('variants', function ($q) use ($minPrice, $maxPrice) {
                $q->whereBetween('price', [$minPrice, $maxPrice]);
            });
        }


        if (!empty($request->type)) {
            if (is_array($request->type)) {
                $query->whereIn('type', $request->type);
            } else {
                $query->where('type', $request->type);
            }
        }

        if (isset($request->min_rating)) {
            $minRating = $request->min_rating;
            // Subquery to calculate the average rating for each product
            $averageRatingSubquery = DB::table('reviews')
                ->select('reviewable_id', DB::raw('AVG(rating) as average_rating'))
                ->where('reviewable_type', Product::class)
                ->where('status', 'active')
                ->groupBy('reviewable_id');

            // Join the subquery with the products table
            $query->joinSub($averageRatingSubquery, 'product_ratings', function ($join) {
                $join->on('products.id', '=', 'product_ratings.reviewable_id');
            })
                ->where('product_ratings.average_rating', '>=', $minRating);
        }

        if ($userLat && $userLng) {

            $radius = $request->radius ?? 10;
            $baseQuery = clone $query;

            $distanceExpr = '(6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            ))';

            $locationQuery = $query
                ->select('products.*')
                ->join('product_stocks', 'product_stocks.product_id', '=', 'products.id')
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->selectRaw("$distanceExpr AS distance", [$userLat, $userLng, $userLat])
                // whereRaw instead of having() — survives the pagination count query
                ->whereRaw("$distanceExpr < ?", [$userLat, $userLng, $userLat, $radius])
                //   orderByRaw instead of orderBy('distance') alias
                ->orderByRaw("$distanceExpr ASC", [$userLat, $userLng, $userLat]);

            //   clone before exists() so take(1) doesn't mutate $locationQuery
            if ((clone $locationQuery)->exists()) {
                $query = $locationQuery;
                $useLocationFilter = true;
            } else {
                $query = $baseQuery;
                $useLocationFilter = false;
            }

        }

        if (isset($request->sort)) {
            switch ($request->sort) {
                case 'price_low_high':
                case 'price_high_low':
                    $aggregateFunction = $request->sort === 'price_low_high' ? 'MIN' : 'MAX';
                    $query->addSelect([
                        'effective_price' => DB::table('product_variants')
                            ->selectRaw("{$aggregateFunction}(
                       CASE
                        WHEN flash_sale_products.id IS NOT NULL THEN
                            CASE flash_sales.discount_type
                                WHEN 'amount' THEN
                                    CASE
                                        WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                            product_variants.special_price - flash_sales.discount_amount
                                        ELSE
                                            product_variants.price - flash_sales.discount_amount
                                    END
                                WHEN 'percentage' THEN
                                    CASE
                                        WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                            product_variants.special_price - (product_variants.special_price * flash_sales.discount_amount / 100)
                                        ELSE
                                            product_variants.price - (product_variants.price * flash_sales.discount_amount / 100)
                                    END
                                ELSE
                                    CASE
                                        WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 THEN
                                            product_variants.special_price
                                        ELSE
                                            product_variants.price
                                    END
                            END
        
                        WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 AND product_variants.special_price < product_variants.price THEN
                            product_variants.special_price
        
                        ELSE
                            product_variants.price
                        END
                        )")
                            ->leftJoin('flash_sale_products', function ($join) {
                                $join->on('flash_sale_products.product_id', '=', 'product_variants.product_id');
                            })
                            ->leftJoin('flash_sales', function ($join) {
                                // All conditions INSIDE the join — keeps it a true LEFT JOIN
                                $join->on('flash_sales.id', '=', 'flash_sale_products.flash_sale_id')
                                    ->where('flash_sales.status', 'active')
                                    ->whereDate('flash_sales.start_time', '<=', now())
                                    ->whereDate('flash_sales.end_time', '>=', now());
                            })
                            ->whereColumn('product_variants.product_id', 'products.id')
                    ])
                        ->orderBy('effective_price', $request->sort === 'price_low_high' ? 'asc' : 'desc');
                    break;

                case 'newest':
                    $query->orderBy('products.created_at', 'desc');
                    break;

                default:
                    $query->latest('products.created_at');
            }
        }



        if (!empty($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('products.name', 'like', '%' . $request->search . '%')
                    ->orWhere('products.description', 'like', '%' . $request->search . '%');
            });
        }

        // Pagination
        $perPage = $request->per_page ?? 20;

        $products = $query->with([
            'category',
            'unit',
            'tags',
            'brand',
            'stocks' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            },
            'variants' => function ($query) use ($request) {
                $shouldRound = shouldRound();

                $discountAmountExpr = $shouldRound
                    ? 'ROUND(flash_data.discount_amount)'
                    : 'flash_data.discount_amount';

                $discountSpecialPricePercentExpr = $shouldRound
                    ? 'ROUND(product_variants.special_price * flash_data.discount_amount / 100)'
                    : '(product_variants.special_price * flash_data.discount_amount / 100)';

                $discountBasePricePercentExpr = $shouldRound
                    ? 'ROUND(product_variants.price * flash_data.discount_amount / 100)'
                    : '(product_variants.price * flash_data.discount_amount / 100)';

                $priceExpr = "
            CASE
                WHEN flash_data.fsp_id IS NOT NULL THEN
                    CASE flash_data.discount_type
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
                WHEN product_variants.special_price IS NOT NULL AND product_variants.special_price > 0 AND product_variants.special_price < product_variants.price THEN
                    product_variants.special_price
                ELSE
                    product_variants.price
            END
        ";

                $finalExpr = $shouldRound ? "ROUND($priceExpr)" : "FORMAT($priceExpr, 2)";

                $flashSubquery = DB::table('flash_sale_products as fsp')
                    ->join('flash_sales as fs', function ($join) {
                        $join->on('fs.id', '=', 'fsp.flash_sale_id')
                            ->whereRaw("fs.status = 1")
                            ->whereRaw("fs.start_time <= NOW()")
                            ->whereRaw("fs.end_time >= NOW()");
                    })
                    ->where('fsp.status', 'active')
                    ->select(
                        'fsp.product_id',
                        'fsp.id as fsp_id',
                        'fs.discount_type',
                        'fs.discount_amount'
                    );

                $query
                    ->leftJoinSub($flashSubquery, 'flash_data', 'flash_data.product_id', '=', 'product_variants.product_id')
                    ->select('product_variants.*')
                    ->selectRaw("$finalExpr as effective_price");

                if ($request->sort === 'price_low_high') {
                    $query->orderByRaw("$finalExpr ASC");
                } elseif ($request->sort === 'price_high_low') {
                    $query->orderByRaw("$finalExpr DESC");
                }
            },'related_translations'
        ])->paginate($perPage);

        // Extract unique attributes from variants
        $uniqueAttributes = $this->getUniqueAttributesFromVariants($products, $request->input('language', 'en'));

         return [
            'messages' => __('messages.data_found'),
            'data' => ProductPublicResource::collection($products),
            'meta' => new PaginationResource($products),
            'filters' => $uniqueAttributes,
            'locationFilter' => $useLocationFilter
         ];
    });

        return response()->json($cached);
    }

    protected function getUniqueAttributesFromVariants($products, ?string $languageCode = 'en')
    {
        $attributes = [];

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                if (!empty($variant->attributes)) {
                    $variantAttributes = json_decode($variant->attributes, true);

                    if (is_array($variantAttributes)) {
                        foreach ($variantAttributes as $key => $value) {
                            $translations = Translation::where('translatable_type', ProductAttribute::class)
                                ->where('value', $key)
                                ->get();

                            // fallback to lowercase match if not found
                            if ($translations->isEmpty()) {
                                $translations = Translation::where('translatable_type', ProductAttribute::class)
                                    ->whereRaw('LOWER(value) = ?', [strtolower($key)])
                                    ->get();
                            }

                            $translatedKey = null;

                            if ($translations->isNotEmpty()) {
                                // get the first match for the requested language
                                $matched = $translations->firstWhere('language', $languageCode);

                                // if not found, fallback to default language (assume 'en')
                                if (!$matched) {
                                    $matched = $translations->firstWhere('language', 'en');
                                }

                                // now find translation of that key in requested language
                                if ($matched) {
                                    $translatedKey = Translation::where('translatable_type', ProductAttribute::class)
                                        ->where('translatable_id', $matched->translatable_id)
                                        ->where('key', $matched->key)
                                        ->where('language', $languageCode)
                                        ->value('value');
                                }
                            }

                            $finalKey = $translatedKey ?? $key;

                            // Initialize if not set
                            if (!isset($attributes[$finalKey])) {
                                $attributes[$finalKey] = [];
                            }

                            // Prevent duplicate values
                            if (!in_array($value, $attributes[$finalKey])) {
                                $attributes[$finalKey][] = $value;
                            }
                        }
                    }
                }
            }
        }

        return $attributes;
    }


    public function productDetails(Request $request, $product_slug)
    {
        $branchId = isWebBranch();

        $product = Product::with([
            'tags',
            'unit',
            'reviews',
            'brand',
            'category',
            'related_translations',
            'fullSpecifications',
            'stocks' => function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            },
            'variants' => function ($q) use ($branchId) {
                $q->with(['stocks' => function ($sq) use ($branchId) {
                    $sq->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                }]);
            },
        ])
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where('slug', $product_slug)
            // only show if branch has stock
            ->whereHas('stocks', function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                    ->where('qty', '>', 0);
            })
            ->first();

        if (!$product) {
            return response()->json([
                'messages' => __('messages.data_not_found'),
                'data' => null
            ], 404);
        }

        if ($product) {
            // Track unique user views
            if (auth('api_customer')->check()) {
                $user = auth('api_customer')->user();

                $viewExists = ProductView::where('product_id', $product->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if (!$viewExists) {
                    $product->increment('views');

                    ProductView::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                    ]);
                }

            } else {
                $ipAddress = $request->ip();
                $viewExists = ProductView::where('product_id', $product->id)
                    ->where('ip_address', $ipAddress)
                    ->exists();
                if (!$viewExists) {
                    $product->increment('views');
                    ProductView::create([
                        'product_id' => $product->id,
                        'ip_address' => $ipAddress,
                    ]);
                }
            }
        }
        return response()->json([
            'messages' => __('messages.data_found'),
            'data' => new ProductDetailsPublicWebResource($product),
            'related_products' => RelatedProductPublicResource::collection($product->relatedProductsWithCategoryFallback())
        ], 200);
    }


    public function homeProductData(Request $request)
    {
        $language  = $request->language ?? 'en';
        $userLat   = $request->user_lat;
        $userLng   = $request->user_lng;
        $availability = $request->availability;
        // flash
        $perPage = $request->per_page ?? 10;
        $radius = $request->radius ?? 10;
        $page = $request->page ?? 1;
        $lang = $request->language ?? 'en';

        // Cache key depends on user location, availability, and limits to avoid collisions
        $cacheKey = "home_product_data:"
            . "lat={$userLat}:lng={$userLng}:availability={$availability}"
            . ":new={$request->new_arrivals_limit}:popular={$request->popular_product_limit}"
            . ":best={$request->best_selling_product_limit}:featured={$request->featured_product_limit}"
            . ":lang={$language}";

        $ttlSeconds = 24 * 60 * 60; // 1 day cache

        // active theme check
        $activeTheme = config('themes.active_theme');
        $product_type = $activeTheme === 'theme_two' ? 'flower' : 'furniture';

        $branchId = isWebBranch();

        // Get from cache or run the query
        $data = Cache::remember($cacheKey, $ttlSeconds, function () use ($request, $product_type, $branchId, $language) {
            // Main query - CORRECTED
            $query = Product::query()
                ->select([
                    'products.id',
                    'products.category_id',
                    'products.brand_id',
                    'products.unit_id',
                    'products.name',
                    'products.slug',
                    'products.type',
                    'products.behaviour',
                    'products.description',
                    'products.status',
                    'products.views',
                    'products.order_count',
                    'products.is_featured',
                    'products.image',
                    'products.class',
                    'products.max_cart_qty',
                    'products.deleted_at',
                ])
                ->with([
                    'unit:id,name',
                    'related_translations' => function ($q) use ($language) {
                        $q->where('language', $language);
                    },
                    // branch wise product stock
                    'stocks' => function ($q) use ($branchId) {
                        $q->select('id', 'branch_id', 'product_id', 'variant_id', 'qty', 'qty_reserved', 'is_active')
                            ->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    },
                    // branch wise variant stock
                    'variants.stocks' => function ($q) use ($branchId) {
                        $q->select('id', 'branch_id', 'product_id', 'variant_id', 'qty', 'qty_reserved', 'is_active')
                            ->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    },
                    'variants' => function ($query) {
                        $query->select(
                            'id',
                            'product_id',  // needed for relationship
                            'variant_slug',
                            'sku',
                            'pack_quantity',
                            'weight_major',
                            'weight_gross',
                            'weight_net',
                            'attributes',
                            'price',
                            'special_price',
                            'unit_id',
                            'length',
                            'width',
                            'height',
                            'order_count',
                            'status',
                        )->addSelect(DB::raw('
                    CASE 
                        WHEN special_price IS NOT NULL 
                        AND special_price > 0 
                        AND special_price < price 
                         THEN special_price 
                        ELSE price 
                    END as effective_price
                '));
             }])
                ->where('products.type', $product_type)
                ->where('products.status', 'active')
                ->whereNull('products.deleted_at')
                // main stock check branch wise
                ->whereHas('stocks', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId)
                        ->where('qty', '>', 0);
                });

            $popular_query = clone $query;
            $best_selling_query = clone $query;
            $featured_query = clone $query;

            // Location wise product filter
            $userLat = $request->user_lat;
            $userLng = $request->user_lng;
            $availability = $request->availability;
            $new_arrivals_limit = (int) $request->new_arrivals_limit;
            $popular_limit = (int) $request->popular_product_limit;
            $best_sell_limit = (int) $request->best_selling_product_limit;
            $featured_limit = (int) $request->featured_product_limit;
            $useLocationFilter = false;
            $radius = $request->radius ?? 10;

        // popular product
        if ($userLat && $userLng) {
            // Clone the base query to apply location filter
            $locationQuery = clone $popular_query;
            $locationQuery->distinct()
                ->join('product_stocks', function ($join) use ($branchId) {
                    $join->on('product_stocks.product_id', '=', 'products.id')
                        ->where('product_stocks.branch_id', $branchId)
                        ->where('product_stocks.qty', '>', 0)
                        ->where('product_stocks.is_active', 1);
                })
                ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                ->selectRaw('
            (6371 * acos(
                cos(radians(?)) *
                cos(radians(branches.latitude)) *
                cos(radians(branches.longitude) - radians(?)) +
                sin(radians(?)) *
                sin(radians(branches.latitude))
            )) AS distance
            ', [$userLat, $userLng, $userLat])
                    ->having('distance', '<', $radius)
                    ->orderBy('distance');

                // Test if location-filtered query returns any product
                $locationResults = (clone $locationQuery)->select('products.id')->limit(1)->exists();

                if ($locationResults) {
                    $popular_query = clone $locationQuery;
                    $best_selling_query = clone $locationQuery;
                    $featured_query = clone  $locationQuery;
                    $useLocationFilter = true;
                }
        }

        // new arrivals products
        if (!empty($availability)) {
            $query->whereHas('variants', fn($q) => $q->where('stock_quantity', $availability ? '>' : '=', 0));
        }

        $new_arrivals = $query->latest()
            ->limit($new_arrivals_limit)
            ->get();

        // popular products
        $popular_query->orderByDesc('views');

        // best selling products
        $best_selling_query->orderByDesc('order_count');


        // featured products
        $featured_query->where('products.is_featured', true);

       // popular products
        $popular_products = $popular_query
        ->where('products.status', 'active')
        ->whereNull('products.deleted_at')
        ->limit($popular_limit)
        ->get();

       // best  selling
        $best_selling_products = $best_selling_query
        ->where('products.status', 'active')
        ->whereNull('products.deleted_at')
        ->limit($best_sell_limit)
        ->get();

         // featured products
        $featured_products = $featured_query
        ->where('products.status', 'active')
        ->whereNull('products.deleted_at')
        ->limit($featured_limit)
        ->get();

          // flash deals data
          $flash_deals_data = $this->flashSaleService->getValidFlashSales();

           // flash sell data
            $flash_sale_query = FlashSaleProduct::query()->with([
                'product.category',
                'product.unit',
                'product.tags',
                'product.brand',
                'product.related_translations',
                'product.variants',
                'flashSale.related_translations'
            ])->whereHas('flashSale', function ($flash_sale_query) {
                $flash_sale_query->where('status', 1);
            });

            if ($userLat && $userLng) {
                $radius = $request->radius ?? 10;
                // Base query backup
                $baseQuery = clone $flash_sale_query;

                // Build location-aware flash sale product filter
                $locationQuery = $flash_sale_query
                    ->join('products', 'products.id', '=', 'flash_sale_products.product_id')
                    ->join('product_stocks', 'product_stocks.product_id', '=', 'products.id')
                    ->join('branches', 'branches.id', '=', 'product_stocks.branch_id')
                    ->select('flash_sale_products.*')
                    ->selectRaw('
                    (6371 * acos(
                        cos(radians(?)) *
                        cos(radians(branches.latitude)) *
                        cos(radians(branches.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(branches.latitude))
                    )) AS distance
                ', [$userLat, $userLng, $userLat])
                    ->having('distance', '<', $radius)
                    ->orderBy('distance');

                if ($locationQuery->take(1)->exists()) {
                    $flash_sale_query = $locationQuery;
                    $useLocationFilter = true;
                } else {
                    $flash_sale_query = $baseQuery;
                    $useLocationFilter = false;
                }
            }

            $flash_sell_limit = (int) $request->flash_sell_limit;

            // Pagination
            $flashSaleProducts = $flash_sale_query
                ->limit($flash_sell_limit ?? 10)
                ->get();

            return [
                'data' => [
                    'new_arrivals' => NewArrivalPublicHomeResource::collection($new_arrivals),
                    'popular_products' => ProductPublicHomeResource::collection($popular_products),
                    'best_selling_products' => ProductPublicHomeResource::collection($best_selling_products),
                    'featured_products' => ProductPublicHomeResource::collection($featured_products),
                    'flash_deals' => $flash_deals_data ? FlashSaleWithProductPublicResource::collection($flash_deals_data) : null,
                    'flash_sale_products' => FlashSaleAllProductPublicResource::collection($flashSaleProducts),
                ],
            ];
        });

        // Return cached data as JSON
        return response()->json($data, 200);
    }

    public function productCategoryList(Request $request)
    {
        try {
            $per_page = $request->per_page ?? 100;
            $language = $request->language ?? config('app.default_language');
            $search = $request->search;
            $sort = $request->sort ?? 'asc';
            $sortField = $request->sortField ?? 'id';
            $type = $request->type; // Get the type filter
            $all = $request->all ?? false;



            $categories = ProductCategory::leftJoin('translations', function ($join) use ($language) {
                $join->on('product_category.id', '=', 'translations.translatable_id')
                    ->where('translations.translatable_type', '=', ProductCategory::class)
                    ->where('translations.language', '=', $language)
                    ->where('translations.key', '=', 'category_name');
            })->select('product_category.*', DB::raw('COALESCE(translations.value, product_category.category_name) as category_name'));

            // Apply type filter if type is provided
            if (!empty($type)){
                $categories->where('product_category.type', $type);
            }else{
                $categories->where('product_category.type', getThemeProductType());
            }


            // Apply search filter if search parameter exists
            if ($search) {
                $categories->where(function ($query) use ($search) {
                    $query->where('translations.value', 'like', "%{$search}%")
                        ->orWhere('product_category.category_name', 'like', "%{$search}%");
                });
            }

            // Apply sorting and pagination
            if ($all) {
                $categories = $categories
                    ->where('status', 1)
                    ->orderBy($sortField, $sort)
                    ->paginate($per_page);

                return response()->json([
                    'message' => __('messages.data_found'),
                    'data' => ProductCategoryPublicResource::collection($categories),
                    'meta' => new PaginationResource($categories)
                ], 200);
            }


            $categories = $categories->whereNull('parent_id')
                ->where('status', 1)
                ->orderBy($sortField, $sort)
                ->paginate($per_page);


            return response()->json([
                'message' => __('messages.data_found'),
                'data' => ProductCategoryResource::collection($categories),
                'meta' => new PaginationResource($categories)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function categoryWiseProducts(Request $request)
    {
        try {
            $query = Product::query();
            // Apply category filter
            if (isset($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }
            // Apply price range filter
            if (isset($request->min_price) && isset($request->max_price)) {
                $minPrice = $request->min_price;
                $maxPrice = $request->max_price;

                $query->whereHas('variants', function ($q) use ($minPrice, $maxPrice) {
                    $q->whereBetween('price', [$minPrice, $maxPrice]);
                });
            }
            // Apply brand filter
            if (isset($request->brand_id)) {
                $query->where('brand_id', $request->brand_id);
            }

            // Apply availability filter
            if (isset($request->availability)) {
                $availability = $request->availability;

                if ($availability) {
                    $query->whereHas('variants', fn($q) => $q->where('stock_quantity', '>', 0));
                } else {
                    $query->whereHas('variants', fn($q) => $q->where('stock_quantity', '=', 0));
                }
            }

            // Apply sorting
            if (isset($request->sort)) {
                switch ($request->sort) {
                    case 'price_low_high':
                        $query->orderByHas('variants', fn($q) => $q->orderBy('price', 'asc'));
                        break;

                    case 'price_high_low':
                        $query->orderByHas('variants', fn($q) => $q->orderBy('price', 'desc'));
                        break;

                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;

                    default:
                        $query->latest();
                }
            }

            // Pagination
            $perPage = $request->per_page ?? 10;
            $products = $query->with(['category', 'unit', 'tags', 'brand', 'variants', 'related_translations'])->paginate($perPage);

            return response()->json([
                'message' => 'Products fetched successfully',
                'data' => ProductPublicResource::collection($products),
                'meta' => new PaginationResource($products)
            ], 200);

        } catch (\Exception $e) {

        }
    }

    public function zones()
    {
        $zones = Zone::where('status', 1)
            ->latest()
            ->get();

        return response()->json(ComZoneListForDropdownResource::collection($zones));
    }

    public function tags()
    {
        $tags = Tag::all();
        return TagPublicResource::collection($tags);
    }

    public function productAttributes()
    {
        $attributes = ProductAttribute::where('status', 1)->get();
        return response()->json(ProductAttributeResource::collection($attributes));
    }

    public function brands(Request $request)
    {
        // If request has limit
        $limit = $request->limit ?? 10;
        // If request has language
        $language = $request->language ??  config('app.default_language');
        //Search parameters
        $search = $request->search;
        // Extract brands table with translations table with condition
        $brands = ProductBrand::leftJoin('translations', function ($join) use ($language) {
            $join->on('product_brand.id', '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', ProductBrand::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'brand_name');
        })
            ->select(
                'product_brand.*',
                DB::raw('COALESCE(translations.value, product_brand.brand_name) as brand_name')
            );

        // Apply search filter if search parameter exists
        if ($search) {
            $brands->where(function ($query) use ($search) {
                $query->where('translations.value', 'like', "%{$search}%")
                    ->orWhere('product_brand.brand_name', 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        $brands = $brands->orderBy($request->sortField ?? 'id', $request->sort ?? 'asc')
            ->paginate($limit);

        // Return a collection of ProductBrandResource (including the image)
        return response()->json(ProductBrandPublicResource::collection($brands));
    }

    public function productTypes()
    {
        $storeTypes = ProductType::where('status', 1)->get();

        return response()->json(StoreTypeDropdownPublicResource::collection($storeTypes));
    }

    public function behaviourList()
    {
        $behaviours = collect(Behaviour::cases())->map(function ($behaviour) {
            return [
                'value' => $behaviour->value,
                'label' => ucfirst(str_replace('-', ' ', $behaviour->value)),
            ];
        });

        return response()->json(BehaviourPublicResource::collection($behaviours));
    }

    public function units()
    {
        $units = Unit::all();
        return response()->json(ProductUnitPublicResource::collection($units));
    }

    public function customers(Request $request)
    {
        $query = Customer::where('status', 1);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('last_name', 'LIKE', '%' . $request->search . '%');
            });
        }

        $customers = $query->orderBy('first_name')->limit(50)->get();

        return response()->json(CustomerPublicResource::collection($customers));
    }

    public function orderRefundReasons(Request $request)
    {
        $filters = [
            'per_page' => $request->per_page,
            'search' => $request->search,
        ];
        $reasons = $this->orderRefundRepo->order_refund_reason_list($filters);
        return response()->json([
            'data' => OrderRefundReasonResource::collection($reasons),
            'meta' => new PaginationResource($reasons)
        ], 200);
    }


    public function coupons(Request $request)
    {
        $query = CouponLine::query();

        // Filter by discount type
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        // Sorting by discount (highest to lowest)
        if ($request->sort_by_discount) {
            $query->orderBy('discount', 'desc');
        }

        // Filter by date range (start_date & end_date)
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }

        // Filter for coupons expiring soon (default: within 2 days)
        if ($request->filled('expire_soon') && $request->expire_soon) {
            $days = $request->input('expire_soon_days', 2); // Default to 2 days
            $query->whereBetween('end_date', [now(), now()->addDays($days)]);
        }

        // Search by coupon title & description
        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->whereHas('coupon', function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Sorting by newest (default: descending)
        if ($request->filled('newest') && $request->newest) {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('created_at', 'asc');
        }

        // Pagination
        $perPage = $request->input('per_page', 10); // Default to 10 items per page
        $today = now()->toDateString();
        if (auth('api_customer')->check()) {
            $coupon = $query->with('coupon.related_translations')
                ->whereHas('coupon', function ($q) {
                    $q->where('status', 1);
                })
                ->where('status', 1)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereNull('customer_id')
                ->orWhere('customer_id', auth('api_customer')->user()->id)
                ->paginate($perPage);
        } else {
            $coupon = $query->with('coupon.related_translations')
                ->whereHas('coupon', function ($q) {
                    $q->where('status', 1);
                })
                ->where('status', 1)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereNull('customer_id')
                ->paginate($perPage);
        }

        return response()->json([
            'data' => CouponPublicResource::collection($coupon),
            'meta' => new PaginationResource($coupon)
        ], 200);
    }


    public function page(Request $request, $slug)
    {

        $page = Page::where('slug', $slug)
            ->where('theme_name', 'default')
            ->where('status', 'publish')
            ->first();

        $config_theme = config('themes.active_theme') ?? config('themes.default_theme');

        if (!$page){
            $page = Page::with('related_translations')
                ->where('slug', $slug)
                ->where('theme_name', $config_theme ?? 'default')
                ->where('status', 'publish')
                ->first();
        }

        if (!$page) {
            return response()->json([
                'message' => __('Page Not Found')
            ], 404);
        }


        if ($page->slug === 'about' | $page->slug === 'contact') {

            // check pages type
            if($page->theme_name === 'default'){
                $page = Page::with('related_translations')
                    ->where('slug', $slug)
                    ->where('theme_name','default')
                    ->where('status', 'publish')
                    ->first();
            }else{
                $page = Page::with('related_translations')
                    ->where('slug', $slug)
                    ->where('theme_name', $config_theme ?? 'default')
                    ->where('status', 'publish')
                    ->first();
            }

            if (!$page) {
                return response()->json([
                    'message' => __('messages.data_not_found')
                ], 404);
            }

            // Process content (decode JSON and format images)
            $processedContent = is_string($page->content) ? json_decode($page->content, true) : $page->content;
            $content = is_array($processedContent) ? jsonImageModifierFormatter($processedContent) : [];


            $page->content = $content;

            if ($page->slug === 'about') {
                return response()->json(new ContactUsPublicResource($page));
            }elseif ($page->slug === 'contact'){
                return response()->json(new ContactUsPublicResource($page));
            }
        }

        if (!$page) {
            return response()->json([
                'message' => __('Page Not Found')
            ], 404);
        }

        // Process content (decode JSON and format images)
        $processedContent = is_string($page->content) ? json_decode($page->content, true) : $page->content;
        $formattedContent = is_array($processedContent) ? jsonImageModifierFormatter($processedContent) : [];

        $page->content = $formattedContent;

        return response()->json(new PrivacyPolicyResource($page));
    }

    public function pages(Request $request)
    {
        $pages = Page::with('related_translations')
            ->where('status', 'publish')
            ->take(500)->get();

        return response()->json([
            'all_pages' => PageListResource::collection($pages),
        ]);
    }


    public function checkOutPageExtraInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $flashDealProducts = FlashSaleProduct::with(['flashSale', 'product'])
            ->whereIn('product_id', $request->product_ids ?? [])
            ->get();

        $system_charge = SystemCharge::first();
        $additionalCharge = ProductType::select('name','type', 'description', 'charge_status', 'charge_name', 'charge_amount', 'charge_type')->where('status', 1)->get();


        return response()->json([
            'flash_sale' => $flashDealProducts->map(function ($item) {
                return [
                    'flash_sale_id' => $item->flashSale?->id,
                    'discount_type' => $item->flashSale?->discount_type,
                    'discount_amount' => $item->flashSale?->discount_amount,
                    'purchase_limit' => $item->flashSale?->purchase_limit,
                ];
            })
                ->unique('flash_sale_id') // keep only unique flash sales
                ->values(),
            'flash_sale_products' => FlashSaleAllProductPublicResource::collection($flashDealProducts),
            'additional_charge' => $additionalCharge,
            'order_include_tax_amount' => $system_charge->order_include_tax_amount,
            'order_tax' => $system_charge->order_tax,
        ]);
    }


    public function branchList(Request $request)
    {
        $branches = Branch::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->where('status', 1)
            ->limit(500)
            ->get();

        return response()->json([
            'status' => true,
            'data' => BranchDetailsPublicResource::collection($branches)
        ]);
    }

    public function branches(Request $request)
    {
        $branches = Branch::query()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%");
            })
            ->where('status', 1)
            ->limit(500)
            ->get();

        return response()->json([
            'status' => true,
            'data' => BranchPublicDropdownResource::collection($branches)
        ]);
    }


}
