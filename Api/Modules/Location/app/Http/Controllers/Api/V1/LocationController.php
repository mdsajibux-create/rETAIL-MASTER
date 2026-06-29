<?php

namespace Modules\Location\app\Http\Controllers\Api\V1;


use App\Http\Controllers\Api\V1\Controller;
use Illuminate\Http\Request;
use Modules\Location\app\Models\Area;
use Modules\Location\app\Models\City;
use Modules\Location\app\Models\State;
use Modules\Location\app\Transformers\AreaPublicResource;
use Modules\Location\app\Transformers\AreaResource;
use Modules\Location\app\Transformers\CityPublicResource;
use Modules\Location\app\Transformers\StatePublicResource;
use Modules\Location\app\Transformers\StateResource;

class LocationController extends Controller
{
    /**
     * Get all active states (for initial page load / dropdown).
     */
    public function states(Request $request)
    {
        $states = State::with('translations')->active()
            ->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->name . '%');
            })
            ->limit(500)
            ->get(['id', 'name', 'code', 'delivery_charge']);

        if ($states->count() === 0) {
            return response()->json([
                'status' => false,
                'message' => 'No states found',
            ]);
        }

        return response()->json([
            'data' => StatePublicResource::collection($states),
        ]);
    }

    /**
     * Get active cities for a given state.
     */
    public function citiesByState($state_id = null)
    {
        $data = [];

        City::with('translations')->where('state_id', $state_id)
            ->select('id', 'state_id', 'name', 'delivery_charge')
            ->chunk(500, function ($cities) use (&$data) {
                foreach ($cities as $city) {
                    $data[] = new CityPublicResource($city);
                }
            });

        return response()->json([
            'data' => $data
        ]);
    }

    /**
     * Get active areas for a given city.
     */
    public function areasByCity($city_id)
    {
        $areas = Area::with('translations')->where('city_id', $city_id)->get([
            'id',
            'city_id',
            'name',
            'zip_code',
           'delivery_charge'
        ]);

        return response()->json([
            'data' => AreaPublicResource::collection($areas)
        ]);
    }

    /**
     * Full location tree — all active states → cities → areas.
     * Useful for pre-loading a full dropdown cascade on the frontend.
     */
    public function fullTree()
    {
        $states = State::active()
            ->ordered()
            ->with([
                'activeCities' => fn($q) => $q->with('activeAreas'),
            ])
            ->get();

        return response()->json([
                'data' => StateResource::collection($states)]
        );
    }

    /**
     * Get full detail for a specific area (e.g. after user selects one at checkout).
     */
    public function areaDetail(Area $area)
    {

        if (!$area->is_active) {
            return response()->json([
                'message' => 'Area not available.'
            ], 404);
        }

        $area->load('city.state');

        return response()->json([
            'data' => new AreaResource($area)
        ]);
    }
}
