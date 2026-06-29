<?php

namespace Modules\Location\app\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\V1\Controller;
use App\Http\Resources\Com\PaginationResource;
use App\Interfaces\LocationManageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\Location\app\Http\Requests\AreaRequest;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Transformers\AreaResource;

class AreaController extends Controller
{
    public function __construct(protected LocationManageInterface $locationRepo)
    {
    }

    public function areas(Request $request)
    {
        $areas = Area::query()
            ->when($request->city_id, fn($q, $v) => $q->where('city_id', $v))
            ->when($request->search, fn($q, $v) => $q->where('name', 'like', "%{$v}%")->orWhere('zip_code', 'like', "%{$v}%"))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->with(['city.state'])
            ->ordered()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'data' => AreaResource::collection($areas),
            'meta' => new PaginationResource($areas),
        ]);
    }

    /**
     * Create a new area.
     */
    public function areaAdd(AreaRequest $request)
    {
      $area =  Area::create($request->validated());
        // translations save
        saveTranslations($request, $area);

        return response()->json([
            'message' => 'Area created successfully.',
        ], 201);
    }

    /**
     * Show a single area with city and state.
     */
    public function areaDetails($id)
    {
       $area = Area::with('city.state', 'translations')
            ->where('id', $id)
            ->first();

        return response()->json([
            'data' => new AreaResource($area)
        ]);
    }

    /**
     * Update an existing area.
     */
    public function areaUpdate(AreaRequest $request)
    {
        $area = Area::find((int)$request->id);

        if (!$area){
            return response()->json([
                'message' => 'Area not found.',
            ],404);
        }

        $area->update($request->validated());
        saveTranslations($request, $area);

        return response()->json([
            'status'  => true,
            'message' => 'Area updated successfully.',
        ], 200);
    }

    /**
     * Soft-delete an area.
     */
    public function areaDelete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:areas,id',
        ]);

        $area = Area::find($request->id);

        if (! $area) {
            return response()->json([
                'status' => false,
                'message' => __('messages.data_found')
            ], 404);
        }

        $area->translations()->delete();
        $area->forceDelete();

        return response()->json([
            'status' => true,
            'message' => 'Area deleted successfully.'
        ], 200);
    }

    public function areaUpdateStatus(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'id' => 'required|integer|exists:areas,id',
            'is_active' => 'required|boolean',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validated->errors()
            ]);
        }

        $area = Area::find($request->id);
        $area->update([
            'is_active' => $request->boolean('is_active')
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Area status updated successfully.',
        ], 200);
    }
}
