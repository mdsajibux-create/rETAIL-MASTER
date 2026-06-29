<?php

namespace Modules\Subscription\app\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Subscription\app\Models\Subscription;
use Modules\Subscription\app\Transformers\AdminSubscriptionPackageDetailsResource;
use Modules\Subscription\app\Transformers\AdminSubscriptionPackageResource;

class AdminSubscriptionPackageController extends Controller
{
    public function __construct(protected Subscription $subscription, protected Translation $translation)
    {
    }

    public function translationKeys(): mixed
    {
        return $this->subscription->translationKeys;
    }

    public function index(Request $request)
    {
        // Paginate the packages, 10 items per page
        $query = Subscription::with('related_translations');
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }
        $packages = $query->paginate($request->per_page ?? 10);
        return response()->json([
            'success' => true,
            'packages' => AdminSubscriptionPackageResource::collection($packages),
            'meta' => new PaginationResource($packages),
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'validity' => 'required|integer',
            'price' => 'required|numeric',
            'pos_system' => 'nullable|boolean',
            'self_delivery' => 'nullable|boolean',
            'mobile_app' => 'nullable|boolean',
            'live_chat' => 'nullable|boolean',
            'order_limit' => 'nullable|integer',
            'product_limit' => 'nullable|integer',
            'product_featured_limit' => 'nullable|integer',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);  // 422 Unprocessable Entity
        }

        $subscription = Subscription::create($validator->validated());
        createOrUpdateTranslation($request, $subscription->id, Subscription::class, $this->translationKeys());

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription package created successfully',
        ], 201);
    }

    public function show($id)
    {
        $package = Subscription::findOrFail($id);
        return response()->json([
            'success' => true,
            'package' => new AdminSubscriptionPackageDetailsResource($package),
        ]);
    }

    public function update(Request $request)
    {
        $subscription = Subscription::find($request->id);
        createOrUpdateTranslation($request, $subscription->id, Subscription::class, $this->translationKeys());
        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription package not found',
            ], 404);
        }

        // Validate the incoming request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'image' => 'nullable',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'validity' => 'required|integer',
            'price' => 'required|numeric',
            'pos_system' => 'nullable|boolean',
            'self_delivery' => 'nullable|boolean',
            'mobile_app' => 'nullable|boolean',
            'live_chat' => 'nullable|boolean',
            'order_limit' => 'nullable|integer',
            'product_limit' => 'nullable|integer',
            'product_featured_limit' => 'nullable|integer',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            // Return validation errors
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        $subscription->update($validator->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Subscription package updated successfully',
            'data' => $subscription
        ]);
    }

    public function statusChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:subscriptions,id',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $package = Subscription::findOrFail($request->id);
        if (!$package) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription not found',
            ]);
        }

        $package->update(['status' => $package->status == 0 ? 1 : 0]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status updated successfully',
        ]);
    }

    public function destroy($id)
    {
        $package = Subscription::findOrFail($id);
        $package->delete();
        return response()->json(['message' => 'Package deleted successfully']);
    }
}
