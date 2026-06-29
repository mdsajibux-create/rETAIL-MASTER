<?php

namespace Modules\Location\app\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\LocationManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Location\app\Http\Requests\CityRequest;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;
use Modules\Location\app\Transformers\CityResource;

class CityController extends Controller
{
    public function __construct(protected LocationManageInterface $locationRepo)
    {
    }

    public function cities(Request $request)
    {
        $cities = City::query()
            ->when($request->state_id, fn($q, $v) => $q->where('state_id', $v))
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%"))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->with(['state', 'areas'])
            ->ordered()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => false,
            'data' => CityResource::collection($cities),
            'meta' => new PaginationResource($cities)
        ]);
    }

    /**
     * Create a new city.
     */
    public function citiesAdd(CityRequest $request)
    {
        $city = City::create($request->validated());
        // translations save
        saveTranslations($request, $city);

        return response()->json([
            'message' => 'City created successfully.',
        ], 201);
    }

    /**
     * Show a single city with its state and areas.
     */
    public function citiesDetails($id)
    {
       $city =  City::with(['state', 'areas','translations'])->findOrFail($id);

        return response()->json([
            'data' => new CityResource($city)
        ]);
    }

    /**
     * Update an existing city.
     */
    public function citiesUpdate(CityRequest $request)
    {
        $city = City::findOrFail((int)$request->id);
        $city->update($request->validated());
        saveTranslations($request, $city);

        return response()->json([
            'message' => 'City updated successfully.',
        ]);
    }

    /**
     * delete a city.
     */
    public function citiesDelete(Request $request)
    {
        $city = City::findOrFail($request->id);
        $city->translations()->delete();
        $city->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'City deleted successfully.'
        ], 200);
    }

    /**
     * Toggle active status.
     */
    public function citiesUpdateStatus(Request $request)
    {
        $validated = Validator::make($request->all(), [
           'id' => 'required|integer|exists:cities,id',
           'is_active' => 'required|boolean',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validated->errors()
            ]);
        }

        $city = City::find($request->id);
        $city->update([
            'is_active' => $request->boolean('is_active')
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'City status updated successfully.',
        ], 200);
    }
}
