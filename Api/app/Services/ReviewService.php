<?php

namespace App\Services;

use App\Models\ReviewReaction;
use Modules\Feedback\app\Models\Review;

class ReviewService
{
    public function getAllReviews($filters)
    {
        $query = Review::with([
            'customer',
            'reviewable',
        ]);

        if (isset($filters['customer_name'])) {
            $query->whereHas('customer', function ($customerQuery) use ($filters) {
                $customerQuery->where('first_name', 'like', "%{$filters['customer_name']}%")
                    ->orWhere('last_name', 'like', "%{$filters['customer_name']}%");
            });
        }
        if (isset($filters['reviewable_type'])) {
            if ($filters['reviewable_type'] === 'product') {
                $reviewable_type = 'Modules\Product\app\Models\Product';
            } elseif ($filters['reviewable_type'] === 'delivery_man') {
                $reviewable_type = 'App\Models\User';
            } else {
                $reviewable_type = 'undefined';
            }
            $query->where('reviewable_type', $reviewable_type);
        }

        if (isset($filters['min_rating']) && isset($filters['max_rating'])) {
            $query->whereBetween('rating', [$filters['min_rating'], $filters['max_rating']]);
        }
        if (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }
        if (isset($filters['max_rating'])) {
            $query->where('rating', '<=', $filters['max_rating']);
        }
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }
        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }

    public function addReview($data)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }
        // check reviewable type
        if ($data['reviewable_type'] === 'product') {
            $reviewable_type = 'Modules\Product\app\Models\Product';
        } elseif ($data['reviewable_type'] === 'delivery_man') {
            $reviewable_type = 'App\Models\User';
        } else {
            $reviewable_type = 'undefined';
        }
        // create review
        if (!empty($data)) {
            $review = Review::create([
                "order_id" => $data['order_id'],
                "reviewable_id" => $data['reviewable_id'],
                "reviewable_type" => $reviewable_type,
                "customer_id" => auth('api_customer')->user()->id,
                "review" => $data['review'],
                "rating" => $data['rating'],
            ]);
            if ($review) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getCustomerReviews($filters)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $query = Review::with(['reviewable'])
            ->where('customer_id', auth('api_customer')->user()->id);

        if (isset($filters['min_rating']) && isset($filters['max_rating'])) {
            $query->whereBetween('rating', [$filters['min_rating'], $filters['max_rating']]);
        } elseif (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        } elseif (isset($filters['max_rating'])) {
            $query->where('rating', '<=', $filters['max_rating']);
        } elseif (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['reviewable_type'])) {
            if ($filters['reviewable_type'] === 'product') {
                $reviewable_type = 'Modules\Product\app\Models\Product';
            } elseif ($filters['reviewable_type'] === 'delivery_man') {
                $reviewable_type = 'App\Models\User';
            } else {
                $reviewable_type = 'undefined';
            }
            $query->where('reviewable_type', $reviewable_type);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }
        return $query->latest()->paginate($filters['per_page'] ?? 10);
    }


    public function getDeliverymanReviews($filters, $deliverymanId)
    {
        if (!auth('api')->check()) {
            unauthorized_response();
        }

        $query = Review::with(['reviewable', 'customer'])
            ->where('reviewable_type', 'App\Models\User')
            ->whereIn('reviewable_id', $deliverymanId);

        if (isset($filters['min_rating']) && isset($filters['max_rating'])) {
            $query->whereBetween('rating', [$filters['min_rating'], $filters['max_rating']]);
        } elseif (isset($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        } elseif (isset($filters['max_rating'])) {
            $query->where('rating', '<=', $filters['max_rating']);
        } elseif (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }


        if (isset($filters['reviewable_type'])) {
            if ($filters['reviewable_type'] === 'product') {
                $reviewable_type = 'Modules\Product\app\Models\Product';
            } elseif ($filters['reviewable_type'] === 'delivery_man') {
                $reviewable_type = 'App\Models\User';
            } else {
                $reviewable_type = 'undefined';
            }
            $query->where('reviewable_type', $reviewable_type);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('created_at', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->where('status', 'approved')
            ->latest()
            ->paginate($filters['per_page'] ?? 10);
    }

    public function reaction($reviewId, $reactionType)
    {
        $userId = auth('api_customer')->user()->id;
        $review = Review::findOrFail($reviewId);

        // Fetch existing reaction if any
        $existingReaction = ReviewReaction::where('review_id', $reviewId)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type === $reactionType) {
                // Remove reaction and decrement count
                $existingReaction->delete();
                $review->decrement("{$reactionType}_count");

                return true;
            } else {
                // Switch reaction type (like <-> dislike)
                $oldReactionType = $existingReaction->reaction_type;
                $existingReaction->update(['reaction_type' => $reactionType]);

                // Update counts
                $review->increment("{$reactionType}_count");
                $review->decrement("{$oldReactionType}_count");

                return true;
            }
        }

        ReviewReaction::create([
            'review_id' => $reviewId,
            'user_id' => $userId,
            'reaction_type' => $reactionType,
        ]);

        $review->increment("{$reactionType}_count");
        return true;
    }

    public function bulkApprove(array $ids)
    {
        if (!empty($ids)) {
            $reviews = Review::whereIn('id', $ids)
                ->where('status', 'pending')
                ->where('status', '!=', 'rejected')
                ->update(['status' => 'approved']);
            return $reviews > 0;
        } else {
            return false;
        }
    }

    public function bulkReject(array $ids)
    {
        if (!empty($ids)) {
            $reviews = Review::whereIn('id', $ids)
                ->where('status', 'pending')
                ->where('status', '!=', 'approved')
                ->update(['status' => 'rejected']);
            return $reviews > 0;
        } else {
            return false;
        }
    }

    public function bulkDelete(array $ids)
    {
        if (!empty($ids)) {
            $reviews = Review::whereIn('id', $ids)->delete();
            return $reviews > 0;
        } else {
            return false;
        }
    }
}



