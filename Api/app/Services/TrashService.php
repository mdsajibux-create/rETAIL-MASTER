<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\Branch\app\Models\Branch;
use Modules\Product\app\Models\Product;

class TrashService
{
    protected array $relatedRelations = [
        'customer' => [
            'addresses',
            'reviews',
            'tickets',
            'productQueries',
            'blogComments',
            'chats',
            'sentMessages',
            'receivedMessages',
        ],
        'chat' => ['messages'],
        'ticket' => ['messages'],
        'deliveryman' => [
            'deliveryman'
        ],
        'product' => [
            'reviews',
            'variants',
            'queries',
        ],
    ];

    protected array $models = [
        'customer' => Customer::class,
        'branch' => Branch::class,
        'deliveryman' => User::class,
        'product' => Product::class,
    ];

    protected array $scopes = [
        'deliveryman' => 'delivery_level',
    ];

    protected function getQueryBuilder(string $type)
    {
        $model = $this->models[$type] ?? null;

        if (!$model) {
            throw new \InvalidArgumentException("Invalid model type: {$type}");
        }

        $query = $model::onlyTrashed();

        if (isset($this->scopes[$type])) {
            $query->where('activity_scope', $this->scopes[$type]);
        }

        return $query;
    }

    public function listTrashed(string $type, int $perPage = 10, array $with = [])
    {
        return $this->getQueryBuilder($type)->with($with)->paginate($perPage);
    }

    public function restore(string $type, array $ids): int
    {
        $query = $this->getQueryBuilder($type)->whereIn('id', $ids);
        $restored = $query->restore();

        $modelClass = $this->models[$type];

        foreach ($modelClass::withTrashed()->whereIn('id', $ids)->get() as $item) {
            $this->restoreRelations($type, $item);
        }

        return $restored;
    }

    protected function restoreRelations(string $type, Model $item): void
    {
        $relations = $this->relatedRelations[$type] ?? [];

        foreach ($relations as $relation) {
            if (!method_exists($item, $relation)) {
                continue;
            }

            $relationInstance = $item->$relation();
            $relatedItems = $relationInstance->withTrashed()->get();

            foreach ($relatedItems as $relatedItem) {
                if (method_exists($relatedItem, 'restore') && $relatedItem->trashed()) {
                    $relatedItem->restore();
                }

                $relatedType = $this->guessExplicitRelatedType($type, $relation);
                if ($relatedType) {
                    $this->restoreRelations($relatedType, $relatedItem);
                }
            }
        }
    }

    public function forceDelete(string $type, array $ids): int
    {
        $modelClass = $this->models[$type];
        $items = $modelClass::withTrashed()->whereIn('id', $ids)->get();

        foreach ($items as $item) {
            $this->forceDeleteRelations($type, $item);
            $item->forceDelete();
        }

        return count($items);
    }

    protected function forceDeleteRelations(string $type, Model $item): void
    {
        $relations = $this->relatedRelations[$type] ?? [];

        foreach ($relations as $relation) {
            if (!method_exists($item, $relation)) {
                continue;
            }

            $relationInstance = $item->$relation();
            $relatedItems = $relationInstance->withTrashed()->get();

            foreach ($relatedItems as $relatedItem) {
                $relatedType = $this->guessExplicitRelatedType($type, $relation);

                if ($relatedType) {
                    $this->forceDeleteRelations($relatedType, $relatedItem);
                }

                if (method_exists($relatedItem, 'forceDelete')) {
                    $relatedItem->forceDelete();
                }
            }
        }
    }

    protected function guessExplicitRelatedType(string $parentType, string $relation): ?string
    {
        foreach ($this->relatedRelations as $type => $relations) {
            if ($type === $relation) {
                return $relation; // direct match
            }

            if ($parentType === $type && in_array($relation, $relations, true)) {
                return $this->inferTypeFromRelationName($relation); // fallback
            }
        }

        return null;
    }

    protected function resolveRelationType(string $parentType, string $relation): ?string
    {
        $map = [
            'wallet' => 'wallet',
            'products' => 'product',
            'variants' => null, // If variant is not in $models, skip
            'reviews' => null,
            'queries' => null,
            'chats' => null,
            'sentMessages' => null,
            'receivedMessages' => null,
            'tickets' => null,
            'addresses' => null,
            'productQueries' => null,
            'blogComments' => null,
        ];

        return $map[$relation] ?? null;
    }


    protected function inferTypeFromRelationName(string $relation): string
    {
        return match ($relation) {
            'wallet' => 'wallet',
            'chats' => 'chat',
            'tickets' => 'ticket',
            'messages' => 'message', // fallback if you need
            default => $relation,
        };
    }
}
