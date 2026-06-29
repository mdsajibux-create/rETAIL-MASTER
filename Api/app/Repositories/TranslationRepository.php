<?php

namespace App\Repositories;

use App\Interfaces\TranslationInterface;
use App\Models\Translation;
use Illuminate\Http\Request;

/**
 * Interface TranslationRepositoryRepository.
 *
 * @package namespace App\Repositories;
 */
class TranslationRepository implements TranslationInterface
{
    public function __construct(protected Translation $translation) {}

    public function storeTranslation(Request $request, int|string $refid, string $refPath, array  $colNames): bool
    {
        $translations = [];
        if ($request['translations']) {
            foreach ($request['translations'] as $translation) {
                foreach ($colNames as $key) {

                    // Fallback value if translation key does not exist
                    if (isset($translation[$key])){
                        $translatedValue = $translation[$key] ?? null;
                    }else{
                        $translatedValue = null;
                    }

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

    public function updateTranslation(Request $request, int|string $refid, string $refPath, array  $colNames): bool
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

    public function translationKeys()
    {
        // To be Implemented
    }

    public function index()
    {
        // To be Implemented
    }
    public function getById($id)
    {
        // To be Implemented
    }
    public function store(array $data)
    {
        // To be Implemented
    }
    public function update(array $data, $id)
    {
        // To be Implemented
    }
    public function changeStatus(int|string $id,string $status="")
    {
        // To be Implemented
    }
    public function delete($id)
    {
        // To be Implemented
    }

    /**
     * @inheritDoc
     */
    public function getPaginatedList(int|string $limit,int $page, string $language, string $search, string $sortField, string $sort,array $filters): mixed
    {

    }
}
