<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\WishListRequest;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Customer\WishListResource;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishListManageController extends Controller
{
    public function addToWishlist(WishListRequest $request)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $request['customer_id'] = auth('api_customer')->user()->id;

        $exists = Wishlist::where('customer_id', $request['customer_id'])
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            $exists->delete();

            return $this->success(translate('messages.wishlist_remove', ['name' => 'Product']));
        }

        Wishlist::create(request()->all());

        return $this->success(translate('messages.wishlist_add', ['name' => 'Product']));
    }

    public function removeFromWishlist(Request $request)
    {
        if (!auth('api_customer')->check()) {
            unauthorized_response();
        }

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        Wishlist::where('customer_id', auth('api_customer')->user()->id)
            ->where('product_id', $request->product_id)
            ->delete();

        return $this->success(translate('messages.wishlist_remove', ['name' => 'Product']));

    }

    public function wishlists()
    {
        try {

            $customerId = auth('api_customer')->user()->id;

            // get web branch id however your system stores it
            $branchId = isWebBranch();

            $wishlist = Wishlist::with([
                'product.category',
                'product.unit',
                'product.brand',
                'product.related_translations',
                'product.variants' => function ($q) use ($branchId) {
                    $q->with(['stocks' => function ($sq) use ($branchId) {
                        $sq->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    }]);
                },
            ])
                ->whereHas('product', function ($q) use ($branchId) {
                    $q->whereHas('stocks', function ($sq) use ($branchId) {
                        $sq->where('branch_id', $branchId)
                            ->where('qty', '>', 0);
                    });
                })
                ->where('customer_id', $customerId)
                ->latest()
                ->paginate(10);

            return response()->json([
                'wishlist' => WishlistResource::collection($wishlist),
                'meta' => new PaginationResource($wishlist),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
