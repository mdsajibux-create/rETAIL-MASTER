<?php

namespace Modules\Feedback\app\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Admin\AdminQueriesResource;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\ProductQueryManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminProductQueryManageController extends Controller
{
    public function __construct(protected ProductQueryManageInterface $productQueryRepo)
    {

    }

    public function getAllQueries(Request $request)
    {
        $filters = [
            "search" => $request->search,
            "date_filter" => $request->date_filter,
            "reply_status" => $request->reply_status,
            "status" => $request->status,
            "per_page" => $request->per_page,
        ];

        $queries = $this->productQueryRepo->getAllQuestionsAndReplies($filters);

        return response()->json([
            'data' => AdminQueriesResource::collection($queries),
            'meta' => new PaginationResource($queries),
        ], 200);

    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|exists:product_queries,id',
            'ids.*' => 'required|integer|exists:product_queries,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deleted = $this->productQueryRepo->bulkDelete($request->ids);

        return $deleted
            ? response()->json(['message' => __('messages.delete_success', ['name' => 'Queries'])], 200)
            : response()->json(['message' => __('messages.delete_failed', ['name' => 'Queries'])], 500);
    }

    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:product_queries,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $success = $this->productQueryRepo->changeStatus($request->id);

        if ($success) {
            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Queries status'])
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Queries status'])
            ], 500);
        }
    }
}
