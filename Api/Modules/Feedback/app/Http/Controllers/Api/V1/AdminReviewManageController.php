<?php

namespace Modules\Feedback\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminReviewResource;
use App\Http\Resources\Com\PaginationResource;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminReviewManageController extends Controller
{
    public function __construct(protected ReviewService $reviewService)
    {

    }

    public function index(Request $request)
    {
        $filters = [
            "min_rating" => $request->min_rating,
            "max_rating" => $request->max_rating,
            "reviewable_type" => $request->reviewable_type,
            "customer_name" => $request->customer_name,
            "rating" => $request->rating,
            "status" => $request->status,
            "start_date" => $request->start_date,
            "end_date" => $request->end_date,
            "per_page" => $request->per_page,
        ];

        $reviews = $this->reviewService->getAllReviews($filters);

        return response()->json([
            'data' => AdminReviewResource::collection($reviews),
            'meta' => new PaginationResource($reviews)
        ]);

    }

    public function approveReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids*' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $success = $this->reviewService->bulkApprove($request->ids);

        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.approve.success', ['name' => 'Review request'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.approve.failed', ['name' => 'Review request'])
            ]);
        }
    }

    public function rejectReview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids*' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $success = $this->reviewService->bulkReject($request->ids);

        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.reject.success', ['name' => 'Review request'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.reject.failed', ['name' => 'Review request'])
            ]);
        }
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids*' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $success = $this->reviewService->bulkDelete($request->ids);

        if ($success) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => __('messages.delete_success', ['name' => 'Review'])
            ]);
        } else {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => __('messages.delete_failed', ['name' => 'Review'])
            ]);
        }
    }

}
