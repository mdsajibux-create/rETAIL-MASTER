<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Customer\CustomerReviewResource;
use App\Models\User;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Feedback\app\Models\Review;
use Modules\Order\app\Models\Order;
use Modules\Product\app\Models\Product;

class CustomerReviewManageController extends Controller
{
    public function __construct(protected ReviewService $reviewService)
    {

    }

    public function index(Request $request)
    {
        $filters = [
            "min_rating" => $request->min_rating,
            "max_rating" => $request->min_rating,
            "reviewable_type" => $request->reviewable_type,
            "rating" => $request->rating,
            "status" => $request->status,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "per_page" => $request->per_page,
        ];
        $reviews = $this->reviewService->getCustomerReviews($filters);
        if (!empty($reviews)) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.data_found'),
                'data' => CustomerReviewResource::collection($reviews),
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

    public function submitReview(ReviewRequest $request)
    {
        $customer_id = (int) auth('api_customer')->user()->id;
        $order = Order::findorfail((int)$request->order_id);

        $reviewableType = match ($request->reviewable_type) {
            'delivery_man' => User::class,
            'product' => Product::class,
            default => 'undefined'
        };

        $order_belongs_to_customer = (int) $order->customer_id === $customer_id;

        if (!$order_belongs_to_customer) {
            return response()->json([
                'status' => false,
                'message' => 'This order does not belongs to this customer'
            ], 422);
        }

        $order_is_delivered = $order->status == 'delivered';

        if (!$order_is_delivered) {
            return response()->json([
                'status' => false,
                'message' => 'This order is not delivered yet!'
            ], 422);
        }

        $review_already_exists = Review::where('order_id', $request->order_id)
            ->where('reviewable_id', $request->reviewable_id)
            ->where('reviewable_type', $reviewableType)
            ->exists();

        if ($review_already_exists) {
            return response()->json([
                'status' => false,
                'message' => 'This review already exists!'
            ], 422);
        }
        if ($request->reviewable_type == 'product') {

            $product = Product::find($request->reviewable_id);
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found!'
                ], 404);
            }
        }
        if ($request->reviewable_type == 'delivery_man') {
            $user = User::find($request->reviewable_id);
            $is_deliveryman = $user->isDeliveryman();
            if (!$is_deliveryman && $user) {
                return response()->json([
                    'status' => false,
                    'message' => 'This user is not a delivery man!'
                ], 403);
            }
        }

        $success = $this->reviewService->addReview($request->all());
        if ($success) {
            return response()->json([
                'status' => true,
                'message' => __('messages.save_success', ['name' => 'Review'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => __('messages.save_failed', ['name' => 'Review'])
            ], 500);
        }
    }

    public function react(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'review_id' => 'required|exists:reviews,id',
            'reaction_type' => 'required|in:like,dislike',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $success = $this->reviewService->reaction($request->review_id, $request->reaction_type);
        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.update_success', ['name' => 'Reaction'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => __('messages.update_failed', ['name' => 'Reaction'])
            ]);
        }
    }

}
