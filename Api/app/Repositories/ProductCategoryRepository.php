<?php

namespace App\Repositories;

use App\Helpers\MultilangSlug;
use App\Models\Translation;
use Modules\Catalog\app\Models\ProductCategory;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 *
 * @package namespace App\Repositories;
 */
class ProductCategoryRepository extends BaseRepository
{

    public function model()
    {
        return ProductCategory::class;
    }

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    public function storeProductCategory($request)
    {
        // Check if an id is present in the request
        $categoryId = $request->input('id');

        // Prepare data for category
        $data = [
            'type' => $request['type'],
            'category_name' => $request['category_name'],
            'category_name_paths' => $request['category_name_paths'],
            'parent_path' => $request['parent_path'],
            'parent_id' => $request['parent_id'],
            'is_featured' => filter_var($request['is_featured'], FILTER_VALIDATE_BOOLEAN),
            'admin_commission_rate' => false,
            'meta_title' => $request['meta_title'] ?? null,
            'meta_description' => $request['meta_description'] ?? null,
            'display_order' => $request['display_order'] ?? ((ProductCategory::max('display_order') ?? 0) + 1),
            'category_banner' => $request['category_banner'] ?? null,
            'category_thumb' => $request['category_thumb'] ?? null,
        ];

        if ($categoryId) {
            // Update existing category (excluding `category_slug`)
            $category = ProductCategory::findOrFail($categoryId);
            $category->update($data);
        } else {
            // Create new category (include `category_slug`)
            $data['category_slug'] = MultilangSlug::makeSlug(ProductCategory::class, $request['category_name'], 'category_slug');
            $category = $this->create($data);
        }


        $translations = [];
        $defaultKeys = ['category_name', 'category_slug', 'meta_title', 'meta_description'];
        // Handle translations
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($defaultKeys as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL GU
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
                    }

                    // If key is brand_slug, generate slug from translated category_name
                    if ($key === 'category_slug') {
                        // Generate the slug from the translated category name instead of using the default
                        $translatedValue = MultilangSlug::makeSlug(
                            Translation::class,
                            $translation['category_name'] ?? $data['category_name'], // Use translated category name
                            'value'
                        );
                    }

                    // Collect translation data
                    $translations[] = [
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }

        // Save translations if available
        if (!empty($translations)) {
            // If updating, delete existing translations first
            if ($categoryId) {
                $category->translations()->delete();
            }
            $category->translations()->createMany($translations);
        }

        return $category;
    }


    public function updateProductBrand($request)
    {
        // Prepare data for default category
        $data = [
            'brand_name' => $request['brand_name'],
            'brand_slug' => MultilangSlug::makeSlug(ProductCategory::class, $request['brand_name'], 'brand_slug'),
            'meta_title' => $request['meta_title'],
            'meta_description' => $request['meta_description'],
            'display_order' => 2,
            'brand_logo' => $request['brand_logo'] ?? null,
        ];

        $brand = $this->findOrFail($request->id)->update($data);

        $translations = [];
        $defaultKeys = ['brand_name', 'brand_slug', 'meta_title', 'meta_description'];

        // Handle translations
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($defaultKeys as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // If key is brand_slug, generate slug from translated brand_name
                    if ($key === 'brand_slug') {
                        // Generate the slug from the translated category name instead of using the default
                        $translatedValue = MultilangSlug::makeSlug(
                            Translation::class,
                            $translation['brand_name'] ?? $data['brand_name'], // Use translated category name
                            'value'
                        );
                    }

                    // Collect translation data
                    $translations[] = [
                        'language' => $translation['language_code'],
                        'key' => $key,
                        'value' => $translatedValue,
                    ];
                }
            }
        }

        // Save translations if available
        if (!empty($translations)) {
            $brand->translations()->createMany($translations);
        }

        return $brand;
    }
}
