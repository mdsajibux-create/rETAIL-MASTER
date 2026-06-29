<?php

namespace App\Repositories;

use App\Interfaces\ProductTypeManageInterface;
use App\Models\Translation;
use Illuminate\Http\Request;
use Modules\BusinessSettings\app\Models\ProductType;

class ProductTypeManageRepository implements ProductTypeManageInterface
{
    public function __construct(protected ProductType $storeType, protected Translation $translation)
    {

    }

    public function translationKeys(): mixed
    {
        return $this->storeType->translationKeys;
    }

    public function getAllStoreTypes(array $filters)
    {
        $query = ProductType::with('related_translations');
        if (isset($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', $searchTerm)
                    ->orWhere('description', 'LIKE', $searchTerm)
                    ->orWhereHas('related_translations', function ($q) use ($searchTerm) {
                        $q->whereIn('key', ['name', 'description'])
                            ->where('value', 'LIKE', $searchTerm);
                    });
            });
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $perPage = $filters['per_page'] ?? 10;

        $types = $query->paginate($perPage);

        if (!empty($types)) {
            return $types;
        } else {
            return null;
        }
    }

    public function updateStoreType(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $type = ProductType::find($data['id']);

        if (!$type) {
            return null;
        }

        $type->update([
            'name' => $data['name'],
            'description' => $data['description'],
            'status' => $data['status'] ?? $type->status,
            'image' => $data['image'] ?? $type->image,
            'charge_status' => $data['charge_status'] ?? $type->charge_status,
            'charge_name' => $data['charge_name'] ?? $type->charge_name,
            'charge_amount' => $data['charge_amount'] ?? $type->charge_amount,
            'charge_type' => $data['charge_type'] ?? $type->charge_type,
        ]);

        return $type->id;

    }

    public function getStoreTypeById(int $id)
    {
        $type = ProductType::with('related_translations')->find($id);
        if (!$type) {
            return null;
        }
        return $type;
    }

    public function createOrUpdateTranslation(Request $request, int|string $refid, string $refPath, array $colNames): bool
    {
        if (empty($request['translations'])) {
            return false;  // Return false if no translations are provided
        }

        $translations = [];
        foreach ($request['translations'] as $translation) {
            foreach ($colNames as $key) {
                // Fallback value if translation key does not exist
                $translatedValue = $translation[$key] ?? null;

                // Skip translation if the value is NULL
                if ($translatedValue === null) {
                    continue; // Skip this field if it's NULL
                }

                // Check if a translation exists for the given reference path, ID, language, and key
                $trans = $this->translation
                    ->where('translatable_type', $refPath)
                    ->where('translatable_id', $refid)
                    ->where('language', $translation['language_code'])
                    ->where('key', $key)
                    ->first();

                if ($trans) {
                    // Update the existing translation
                    $trans->value = $translatedValue;
                    $trans->save();
                } else {
                    // Prepare new translation entry for insertion
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

        // Insert new translations if any
        if (!empty($translations)) {
            $this->translation->insert($translations);
        }

        return true;
    }

    public function toogleStatus(int $id)
    {
        $storeType = ProductType::find($id);
        if (!$storeType) {
            return false;
        }
        return $storeType->update([
            'status' => !$storeType->status
        ]);
    }

}
