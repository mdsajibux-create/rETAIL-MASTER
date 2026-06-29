<?php

namespace App\Http\Controllers\Api\V1\Deliveryman;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Deliveryman\DeliverymanReviewResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;

class DeliverymanReviewController extends Controller
{
    public function __construct(protected ReviewService $reviewService)
    {

    }

    public function index(Request $request)
    {
        $user = auth('api')->user();
        $isDeliveryman = $user->activity_scope == 'delivery_level';
        if (!$isDeliveryman) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'This user is not a deliveryman!'
            ]);
        }
        $filters = [
            "min_rating" => $request->min_rating,
            "max_rating" => $request->min_rating,
            "rating" => $request->rating,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "per_page" => $request->per_page,
        ];

        $reviews = $this->reviewService->getDeliverymanReviews($filters, $user->id);

        if (!empty($reviews)) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => DeliverymanReviewResource::collection($reviews),
                'meta' => new PaginationResource($reviews)
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.data_not_found')
            ]);
        }
    }
}
