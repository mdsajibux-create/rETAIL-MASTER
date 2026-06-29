<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ProductQueryRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Customer\CustomerProductQueryResource;
use App\Interfaces\ProductQueryManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerProductQueryController extends Controller
{
    public function __construct(protected ProductQueryManageInterface $productQueryRepo)
    {
    }

    public function askQuestion(ProductQueryRequest $request)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }
        $question = $this->productQueryRepo->askQuestion($request->all());
        if ($question) {
            return response()->json([
                'message' => __('messages.customer_product_query_submitted_successful')
            ], 200);
        } else {
            return response()->json([
                'message' => __('messages.customer_product_query_submitted_failed')
            ], 500);
        }
    }

    public function searchQuestions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $questions = $this->productQueryRepo->searchQuestion($request->all());

        return response()->json([
            'data' => CustomerProductQueryResource::collection($questions),
            'meta' => new PaginationResource($questions),
        ], 200);

    }
}
