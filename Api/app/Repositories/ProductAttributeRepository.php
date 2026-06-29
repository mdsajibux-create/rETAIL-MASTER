<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\Catalog\app\Models\ProductAttribute;
use Modules\Catalog\app\Models\ProductAttributeValue;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Exceptions\RepositoryException;

/**
 *
 * @package namespace App\Repositories;
 */
class ProductAttributeRepository extends BaseRepository
{

    public function model()
    {
        return ProductAttribute::class;
    }

    public function boot()
    {
        try {
            $this->pushCriteria(app(RequestCriteria::class));
        } catch (RepositoryException $e) {
            //
        }
    }

    public function storeProductAttribute($request)
    {
        // Check if an id is present in the request
        $attributeId = $request->input('id');

        // Prepare data for Attribute
        $data = [
            'name' => $request['name'],
            'product_type' => $request['product_type'],
            'created_by' => auth('api')->id(),
        ];

        if ($attributeId) {
            // Update existing Attribute
            $attribute = ProductAttribute::findOrFail($attributeId);
            $attribute->update($data);
        } else {
            // Create new Aattribute
            $attribute = $this->create($data);
        }

        $translations = [];
        $defaultKeys = ['name'];

        // Handle translations
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($defaultKeys as $key) {

                    // Fallback value if translation key does not exist
                    $translatedValue = $translation[$key] ?? null;

                    // Skip translation if the value is NULL
                    if ($translatedValue === null) {
                        continue; // Skip this field if it's NULL
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
            if ($attributeId) {
                $attribute->translations()->delete();
            }
            $attribute->translations()->createMany($translations);
        }

        return $attribute->id;
    }


    public function storeAttributeValues(array $data, int $attributeId)
    {
        try {
            $values = $data['value']; // Assuming 'value' is an array of values

            $insertData = collect($values)->map(function ($value) use ($attributeId) {
                return [
                    'attribute_id' => $attributeId,
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            ProductAttributeValue::insert($insertData);
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public function updateAttributeValues(array $data, int $attributeId)
    {
        try {
            if (!isset($data['value']) || !is_array($data['value'])) {
                throw new \InvalidArgumentException("The 'value' field must be an array.");
            }

            $values = $data['value'];

            // Wrap in a transaction for atomicity
            DB::transaction(function () use ($values, $attributeId) {
                // Delete existing attribute values
                ProductAttributeValue::where('attribute_id', $attributeId)->delete();

                // Prepare and insert new values
                $insertData = collect($values)->map(function ($value) use ($attributeId) {
                    return [
                        'attribute_id' => $attributeId,
                        'value' => $value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                })->toArray();
                if (!empty($insertData)) {
                    ProductAttributeValue::insert($insertData);
                }
            });

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
