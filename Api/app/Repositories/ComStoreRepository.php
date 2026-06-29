<?php

namespace App\Repositories;

use App\Interfaces\ComAreaInterface;
use Modules\Branch\app\Models\Branch;


/**
 *
 * @package namespace App\Repositories;
 */
class ComStoreRepository implements ComAreaInterface
{

    public function __construct(protected Branch $store) {}

    public function model()
    {
        return Branch::class;
    }

    public function translationKeys()
    {
        return  $this->store->translationKeys;
    }

    public function index()
    {
        return null;
    }

    public function getById($id): array
    {
        $store = $this->store->findOrFail($id);
        $translations = $store->translations()->get()->groupBy('language');

        // Initialize an array to hold the transformed data
        $transformedData = [];

        foreach ($translations as $language => $items) {
            $languageInfo = ['language' => $language];
            /* iterate all Column to Assign Language Value */
            foreach ($this->store->translationKeys as $columnName) {
                $languageInfo[$columnName] = $items->where('key', $columnName)->first()->value ?? "";
            }
            $transformedData[] = $languageInfo;
        }

        return [
            'id' => $store->id,
            'code' => $store->code,
            'name' => $store->name,
            'translations' => $transformedData,
        ];
    }


    public function store(array $data): string|object
    {
        $store = $this->store->newInstance();
        foreach ($data as $column => $value) {
            if ($column != 'translations') {
                $store[$column] = $value;
            }
        }
        $store->save();

        return $store;
    }

    public function update(array $data, $id): string|object
    {
        $store = $this->store->findOrFail($id);

        foreach ($data as $column => $value) {
            if ($column <> 'translations') {
                $store[$column] = $value;
            }
        }
        $store->save();

        return $store;
    }

    public function changeStatus(int|string $id, string $status = "")
    {
        $store = $this->store->findOrFail($id);
        $store->status = !$store->status;
        $store->save();
        return $store;
    }

    public function delete($id)
    {
        $store = $this->store->findOrFail($id);
        $store->translations()->delete();
        $store->delete();
        return true;
    }


    /**
     * @inheritDoc
     */
    public function getPaginatedList(int|string $limit,int $page, string $language, string $search, string $sortField, string $sort,array $filters)
    {

    }
}
