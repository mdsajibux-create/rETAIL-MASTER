<?php

namespace App\Repositories;

use App\Helpers\MultilangSlug;
use App\Http\Resources\ProductDetailsResource;
use App\Interfaces\ProductManageInterface;
use App\Interfaces\ProductVariantInterface;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\DynamicField;
use Modules\Catalog\app\Models\ProductSpecification;
use Modules\Catalog\app\Models\ProductTag;
use Modules\Product\app\Models\Product;
use Modules\Product\app\Models\ProductVariant;
use Modules\SystemCore\app\Models\Media;

class ProductManageRepository implements ProductManageInterface
{
    public function __construct(protected Product $product, protected Translation $translation, protected ProductVariantInterface $variantRepo)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->product->translationKeys;
    }

    public function model(): string
    {
        return Product::class;
    }

    // Fetch all products with parameters
    public function getPaginatedProduct(string $status, int|string $limit, int $page, string $language, string $type, string $search, string $sortField, string $sort, array $filters)
    {
        $product = Product::query();

        if ($status) {
            $product->where('status', $status);
        }

        if ($type) {
            $product->where('type', $type);
        }

        $product->leftJoin('translations as name_translations', function ($join) use ($language) {
            $join->on('products.id', '=', 'name_translations.translatable_id')
                ->where('name_translations.translatable_type', '=', Product::class)
                ->where('name_translations.language', '=', $language)
                ->where('name_translations.key', '=', 'name');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Product::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->leftJoin('translations as meta_title_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_title_translations.translatable_id')
                    ->where('meta_title_translations.translatable_type', '=', Product::class)
                    ->where('meta_title_translations.language', '=', $language)
                    ->where('meta_title_translations.key', '=', 'meta_title');
            })
            ->leftJoin('translations as meta_description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_description_translations.translatable_id')
                    ->where('meta_description_translations.translatable_type', '=', Product::class)
                    ->where('meta_description_translations.language', '=', $language)
                    ->where('meta_description_translations.key', '=', 'meta_description');
            })
            ->leftJoin('translations as meta_keywords_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_keywords_translations.translatable_id')
                    ->where('meta_keywords_translations.translatable_type', '=', Product::class)
                    ->where('meta_keywords_translations.language', '=', $language)
                    ->where('meta_keywords_translations.key', '=', 'meta_keywords');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(name_translations.value, products.name) as name'),
                DB::raw('COALESCE(description_translations.value, products.description) as description'),
                DB::raw('COALESCE(meta_title_translations.value, products.meta_title) as meta_title'),
                DB::raw('COALESCE(meta_description_translations.value, products.meta_description) as meta_description'),
                DB::raw('COALESCE(meta_keywords_translations.value, products.meta_keywords) as meta_keywords')
            );

        // Apply search filter if search parameter exists
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', products.name, name_translations.value, products.description, description_translations.value, meta_title_translations.value, meta_description_translations.value, meta_keywords_translations.value)"), 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        return $product->with([
            'variants.stock',
            'variants.stocks.branch'
        ])
        ->orderBy($sortField ?? 'id', $sort ?? 'asc')
        ->paginate($limit);

    }

    // Fetch all products with parameters
    public function getPaginatedProductForBranch(string $status, int|string $limit, int $page, string $language, string $type, string $search, string $sortField, string $sort, array $filters)
    {
        // branch id
        $branch_id = auth('api')->user()->branch_id;

        $product = Product::query()
            ->whereExists(function ($query) use ($branch_id) {
                $query->select(DB::raw(1))
                    ->from('product_stocks')
                    ->whereColumn('product_stocks.product_id', 'products.id')
                    ->where('product_stocks.branch_id', $branch_id);
            });

        if ($status) {
            $product->where('status', $status);
        }

        if ($type) {
            $product->where('type', $type);
        }

        $product->leftJoin('translations as name_translations', function ($join) use ($language) {
            $join->on('products.id', '=', 'name_translations.translatable_id')
                ->where('name_translations.translatable_type', '=', Product::class)
                ->where('name_translations.language', '=', $language)
                ->where('name_translations.key', '=', 'name');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Product::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->leftJoin('translations as meta_title_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_title_translations.translatable_id')
                    ->where('meta_title_translations.translatable_type', '=', Product::class)
                    ->where('meta_title_translations.language', '=', $language)
                    ->where('meta_title_translations.key', '=', 'meta_title');
            })
            ->leftJoin('translations as meta_description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_description_translations.translatable_id')
                    ->where('meta_description_translations.translatable_type', '=', Product::class)
                    ->where('meta_description_translations.language', '=', $language)
                    ->where('meta_description_translations.key', '=', 'meta_description');
            })
            ->leftJoin('translations as meta_keywords_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_keywords_translations.translatable_id')
                    ->where('meta_keywords_translations.translatable_type', '=', Product::class)
                    ->where('meta_keywords_translations.language', '=', $language)
                    ->where('meta_keywords_translations.key', '=', 'meta_keywords');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(name_translations.value, products.name) as name'),
                DB::raw('COALESCE(description_translations.value, products.description) as description'),
                DB::raw('COALESCE(meta_title_translations.value, products.meta_title) as meta_title'),
                DB::raw('COALESCE(meta_description_translations.value, products.meta_description) as meta_description'),
                DB::raw('COALESCE(meta_keywords_translations.value, products.meta_keywords) as meta_keywords')
            );

        // Apply search filter if search parameter exists
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', products.name, name_translations.value, products.description, description_translations.value, meta_title_translations.value, meta_description_translations.value, meta_keywords_translations.value)"), 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        return $product->with([
            'variants' => function ($query) use ($branch_id) {
                $query->with(['stock' => function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                }]);
            }
        ])
            ->orderBy($sortField ?? 'id', $sort ?? 'asc')
            ->paginate($limit);
    }

    public function getPaginatedProductForAdmin(string $status, int|string $limit, int $page, string $language, string $type, string $search, string $sortField, string $sort, array $filters)
    {
        // branch id
        $branch_id = auth('api')->user()->branch_id;

        $product = Product::query();

        if ($status) {
            $product->where('status', $status);
        }

        if ($type) {
            $product->where('type', $type);
        }

        $product->leftJoin('translations as name_translations', function ($join) use ($language) {
            $join->on('products.id', '=', 'name_translations.translatable_id')
                ->where('name_translations.translatable_type', '=', Product::class)
                ->where('name_translations.language', '=', $language)
                ->where('name_translations.key', '=', 'name');
        })
            ->leftJoin('translations as description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'description_translations.translatable_id')
                    ->where('description_translations.translatable_type', '=', Product::class)
                    ->where('description_translations.language', '=', $language)
                    ->where('description_translations.key', '=', 'description');
            })
            ->leftJoin('translations as meta_title_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_title_translations.translatable_id')
                    ->where('meta_title_translations.translatable_type', '=', Product::class)
                    ->where('meta_title_translations.language', '=', $language)
                    ->where('meta_title_translations.key', '=', 'meta_title');
            })
            ->leftJoin('translations as meta_description_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_description_translations.translatable_id')
                    ->where('meta_description_translations.translatable_type', '=', Product::class)
                    ->where('meta_description_translations.language', '=', $language)
                    ->where('meta_description_translations.key', '=', 'meta_description');
            })
            ->leftJoin('translations as meta_keywords_translations', function ($join) use ($language) {
                $join->on('products.id', '=', 'meta_keywords_translations.translatable_id')
                    ->where('meta_keywords_translations.translatable_type', '=', Product::class)
                    ->where('meta_keywords_translations.language', '=', $language)
                    ->where('meta_keywords_translations.key', '=', 'meta_keywords');
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(name_translations.value, products.name) as name'),
                DB::raw('COALESCE(description_translations.value, products.description) as description'),
                DB::raw('COALESCE(meta_title_translations.value, products.meta_title) as meta_title'),
                DB::raw('COALESCE(meta_description_translations.value, products.meta_description) as meta_description'),
                DB::raw('COALESCE(meta_keywords_translations.value, products.meta_keywords) as meta_keywords')
            );

        // Apply search filter if search parameter exists
        if ($search) {
            $product->where(function ($query) use ($search) {
                $query->where(DB::raw("CONCAT_WS(' ', products.name, name_translations.value, products.description, description_translations.value, meta_title_translations.value, meta_description_translations.value, meta_keywords_translations.value)"), 'like', "%{$search}%");
            });
        }

        // Apply sorting and pagination
        return $product->with([
            'variants' => function ($query) use ($branch_id) {
                $query->with(['stock' => function ($q) use ($branch_id) {
                    $q->where('branch_id', $branch_id);
                }]);
            }
        ])
            ->orderBy($sortField ?? 'id', $sort ?? 'asc')
            ->take($limit)
            ->get();
    }


    // create product
    public function store(array $data)
    {
        try {
            $data = Arr::except($data, ['translations']);
            $data['image'] = (string) $data['image'];
            $product = Product::create($data);

            //Set up media binding for main image
            if (!empty($product->image)) {
                $mainImage = Media::find($product->image);
                if ($mainImage) {
                    $mainImage->update([
                        'user_id' => auth('api')->id(),
                        'user_type' => User::class,
                        'usage_type' => 'product_main',
                    ]);
                }
            }

            // If variants exist 
            if (!empty($data['variants']) && is_array($data['variants'])) {
                $variants = array_map(function ($variant) use ($product) {
                    // Generate the variant slug
                    $variant['attributes'] = json_encode($variant['attributes']);
                    $variant['variant_slug'] = $variant['variant'];
                    // sku
                    $sku = generateUniqueSku();
                    $variant['sku'] = $sku;
                    $variant['product_id'] = $product->id;

                    return $variant;

                }, $data['variants']);

                // insert all variants
                foreach ($variants as $variant) {
                    $variant['image'] = (string) $variant['image'];

                    ProductVariant::create($variant);

                    // Set up media binding for variant image
                    if (!empty($variant['image'])) {
                        $variant['image'] = (int) $variant['image'];
                        $variantImage = Media::find($variant['image']);
                        if ($variantImage) {
                            $variantImage->update([
                                'user_id' => auth('api')->id(),
                                'user_type' => User::class,
                                'usage_type' => 'product_variant',
                            ]);
                        }
                    }
                }
            }

            // If specifications exist
            if (!empty($data['specifications']) && is_array($data['specifications'])) {
                foreach ($data['specifications'] as $specification) {
                    ProductSpecification::create([
                        'product_id'            => $product->id,
                        'dynamic_field_id'      => $specification['dynamic_field_id'] ?? null,
                        'dynamic_field_value_id' => $specification['dynamic_field_value_id'] ?? null,
                        'name'                  => $specification['name'] ?? null,
                        'type'                  => $specification['type'] ?? null,
                        'custom_value'          => $specification['custom_value'] ?? null,
                    ]);

                    // Archive dynamic field value if ID exists
                    if (!empty($specification['dynamic_field_id'])) {
                        DynamicField::where('id', $specification['dynamic_field_id'])
                            ->update(['status' => 'archived']);
                    }
                }
            }


            // Product Tag add
            if (!empty($data['tag_ids']) && is_array($data['tag_ids'])) {
                $productTags = [];
                foreach ($data['tag_ids'] as $tagId) {
                    $productTags[] = [
                        'product_id' => $product->id,
                        'tag_id' => $tagId,
                    ];
                }
                ProductTag::insert($productTags);
            }

            return $product->id;

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function storeBulk(array $bulkData)
    {
        try {
            // store the IDs of inserted products for bulk entry
            $productIds = [];

            foreach ($bulkData as $data) {
                // Remove translations if it exists
                $data = Arr::except($data, ['translations']);

                // Create the product
                $product = Product::create($data);
                $productIds[] = $product->id; // Collect the product ID

                // product variants store if variants are not empty
                if (!empty($data['variants']) && is_array($data['variants'])) {
                    $variants = array_map(function ($variant) use ($product) {
                        $variant_slug = MultilangSlug::makeSlug(ProductVariant::class, $variant['variant_slug'], 'variant_slug');
                        $variant['variant_slug'] = $variant_slug;
                        $variant['sku'] = generateUniqueSku();
                        $variant['product_id'] = $product->id;
                        return $variant;
                    }, $data['variants']);
                    ProductVariant::insert($variants);
                }
            }
            return $productIds;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function update(array $data)
    {
        try {
            // translations
            $data = Arr::except($data, ['translations']);

            // product find
            $product = Product::findorfail($data['id']);

            // Update product
            $product->update($data);

            //Set up media binding for main image
            $product = Product::find($data['id']);
            if (!empty($product->image)) {
                $mainImage = Media::find($product->image);
                if ($mainImage) {
                    $mainImage->update([
                        'user_id' => auth('api')->id(),
                        'user_type' => User::class,
                        'usage_type' => 'product_main',
                    ]);
                }
            }

            // Update or create variants
            if (!empty($data['variants']) && is_array($data['variants'])) {
                $variantIds = [];

                foreach ($data['variants'] as $variant) {
                    // Convert attributes to JSON if provided
                    $variant['attributes'] = !empty($variant['attributes']) ? json_encode($variant['attributes']) : null;
                    $variant['variant_slug'] = $variant['variant']; // Assign the generated slug
                    $variant['product_id'] = $product->id;
                    $variant['image'] = (string) $variant['image'];

                    if (!empty($variant['id'])) {
                        // Check if the variant exists
                        $existingVariant = ProductVariant::where('id', $variant['id'])->where('product_id', $product->id)->first();
                        if ($existingVariant) {
                            // Generate SKU if null
                            if (empty($variant['sku'])) {
                                $variant['sku'] = generateUniqueSku();
                            }
                            $existingVariant->update($variant);
                            $variantIds[] = $existingVariant->id;
                        }

                        // Set up media binding for variant image
                        if (!empty($variant['image'])) {
                            $imageId = (int) $variant['image'];
                             $variantImage = Media::find($imageId);
                            if ($variantImage) {
                                $variantImage->update([
                                    'user_id' => auth('api')->id(),
                                    'user_type' => User::class,
                                    'usage_type' => 'product_variant',
                                ]);
                            }
                        }

                    } else {
                        // Generate SKU if null
                        if (empty($variant['sku'])) {
                            $variant['sku'] = generateUniqueSku();
                        }
                        $newVariant = ProductVariant::create($variant);
                        $variantIds[] = $newVariant->id;

                        // Set up media binding for variant image
                        if (!empty($variant['image'])) {
                            $variantImage = Media::find($newVariant->image);
                            if ($variantImage) {
                                $variantImage->update([
                                    'user_id' => auth('api')->id(),
                                    'user_type' => User::class,
                                    'usage_type' => 'product_variant',
                                ]);
                            }
                        }
                    }

                }

                // Delete variants not present in the request
                ProductVariant::where('product_id', $product->id)
                    ->whereNotIn('id', $variantIds)
                    ->forceDelete();
            }


            // Update Product specifications
            if (!empty($data['specifications']) && is_array($data['specifications'])) {
                // specifications delete
                ProductSpecification::where('product_id', $product->id)->delete();
                // specifications create
                foreach ($data['specifications'] as $specification) {
                    ProductSpecification::create([
                        'product_id'            => $product->id,
                        'dynamic_field_id'      => $specification['dynamic_field_id'] ?? null,
                        'dynamic_field_value_id'=> $specification['dynamic_field_value_id'] ?? null,
                        'name'                  => $specification['name'] ?? null,
                        'type'                  => $specification['type'] ?? null,
                        'custom_value'          => $specification['custom_value'] ?? null,
                    ]);

                    // Archive dynamic field value if ID exists
                    if (!empty($specification['dynamic_field_id'])) {
                        DynamicField::where('id', $specification['dynamic_field_id'])
                            ->update(['status' => 'archived']);
                    }
                }
            }

            // Update product tags
            if (!empty($data['tag_ids']) && is_array($data['tag_ids'])) {
                // First, delete existing tags to avoid duplicates
                $product->tags()->delete();

                // Insert the new tags
                $productTags = [];
                foreach ($data['tag_ids'] as $tagId) {
                    $productTags[] = [
                        'product_id' => $product->id,
                        'tag_id' => $tagId,
                    ];
                }
                ProductTag::insert($productTags);
            }

            return $product->id;
        }
        catch (\Throwable $th) {
            throw $th;
        }
    }

    // Delete data
    public function delete(int|string $id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // product details
    public function getProductBySlug(string $slug)
    {
        try {
            $product = Product::with([
                'related_translations',
                'variants',
                'category',
                'brand',
                'unit',
                'variants.stock',
                'fullSpecifications',
            ])
            ->where('slug', $slug)
            ->first();


            if ($product) {
                return response()->json(new ProductDetailsResource($product));

            } else {
                return response()->json([
                    "massage" => __('messages.data_not_found'),
                ], 404);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // Store translation
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

    // Fetch deleted records(true = only trashed records, false = all records with trashed)
    public function records(bool $onlyDeleted = false)
    {
        try {
            switch ($onlyDeleted) {
                case true:
                    $records = Product::onlyTrashed()->get();
                    break;

                default:
                    $records = Product::withTrashed()->get();
                    break;
            }
            return $records;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function changeStatus(array $data): mixed
    {
        try {
            $product = Product::findOrFail($data['id']);
            $product->status = $data['status'];
            $product->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function approvePendingProducts(array $productIds)
    {
        try {
            $products = Product::whereIn('id', $productIds)
                ->where('deleted_at', null)
                ->update([
                    'status' => 'approved'
                ]);
            return true;
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'status_code' => 404
            ]);
        }
    }

    public function getPendingProducts()
    {
        try {
            $products = Product::where('deleted_at', '=', null)
                ->where('status', 'pending')
                ->with(['store.related_translations', 'related_translations'])
                ->latest()
                ->paginate(10);
            return $products;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
