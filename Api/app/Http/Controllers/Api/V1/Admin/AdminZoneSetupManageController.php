<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ComHelper;
use App\Http\Controllers\Api\V1\Controller;
use App\Http\Requests\ZoneCreateRequest;
use App\Http\Requests\StoreAreaSettingsRequest;
use App\Http\Resources\Admin\AdminAreaSettingsDetailsResource;
use App\Http\Resources\Admin\AreaDetailsResource;
use App\Http\Resources\Admin\AreaResource;
use App\Http\Resources\Com\PaginationResource;
use App\Http\Resources\Translation\AreaTranslationResource;
use App\Interfaces\TranslationInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\BusinessSettings\app\Models\Zone;
use Modules\BusinessSettings\app\Models\ZoneSetting;
use Modules\BusinessSettings\app\Models\ZoneSettingRangeCharge;

class AdminZoneSetupManageController extends Controller
{
    public function __construct( protected TranslationInterface $transRepo){

    }

    public function index(Request $request)
    {
        $limit = (int) ($request->limit ?? 10);
        $limit = $limit > 0 ? min($limit, 100) : 10;
        $page = (int) ($request->page ?? 1);
        $language = app()->getLocale() ?? config('app.fallback_locale', 'en');
        $search = trim($request->search ?? '');
        $sortField = $request->sortField ?? 'id';
        $sort = strtolower($request->sort ?? 'asc') === 'desc' ? 'desc' : 'asc';

        $zoneModel = new Zone();
        $tableName = $zoneModel->getTable();

        // Base query
        $query = $zoneModel->leftJoin('translations', function ($join) use ($language, $tableName) {
            $join->on("{$tableName}.id", '=', 'translations.translatable_id')
                ->where('translations.translatable_type', '=', Zone::class)
                ->where('translations.language', '=', $language)
                ->where('translations.key', '=', 'name');
        })->select("{$tableName}.*", \DB::raw('translations.value as name_translated'));

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search, $tableName) {
                $q->where("{$tableName}.name", 'like', "%{$search}%")
                    ->orWhere('translations.value', 'like', "%{$search}%");
            });
        }

        // Order
        if ($sortField === 'name') {
            $query->orderByRaw("COALESCE(translations.value, {$tableName}.name) {$sort}");
        } else {
            $query->orderBy("{$tableName}.{$sortField}", $sort);
        }

        // Pagination
        $zones = $query->paginate($limit, ['*'], 'page', $page);

        // Return resource
        return response()->json([
            'data' => AreaResource::collection($zones),
            'meta' => new PaginationResource($zones),
        ]);
    }

    private function translationKeys(): array
    {
        return (new Zone())->translationKeys;
    }

    public function store(ZoneCreateRequest $request): JsonResponse
    {
        try {
            $zone = $this->prepareAddData($request);
            createOrUpdateTranslation($request, $zone->id, 'App\Models\Zone', $this->translationKeys());

            return $this->success(translate('messages.save_success', ['name' => $request->name]));
        } catch (\Exception $e) {
            return $this->failed(translate('messages.save_failed', ['name' => 'Area']));
        }
    }

    private function prepareAddData($request): Zone
    {
        $coordinates = $request['coordinates'];
        $polygon     = [];


        foreach ($coordinates as $index => $loc) {
            if ($index == 0) {
                $lastLoc = $loc;
            }
            $polygon[] = new Point($loc['lat'], $loc['lng']);
        }

        // Close the polygon by repeating the first point
        $polygon[] = new Point($lastLoc['lat'], $lastLoc['lng']);
        $center = calculateCenterPoint($request['coordinates']);

        // Create Zone
        return Zone::create([
            'state'            => $request->state,
            'city'             => $request->city,
            'name'             => $request->name,
            'code'             => $request->code,
            'coordinates'      => new Polygon([new LineString($polygon)]),
            'center_latitude'  => $center['center_latitude'],
            'center_longitude' => $center['center_longitude'],
            'status'           => $request->status ?? 1,
            'created_by'       => auth('api')->id(),
        ]);
    }

    public function show(Request $request)
    {

        // Find the area by id
        $area = Zone::with('related_translations')
            ->findOrFail($request->id);


        $formated_coordinates = json_decode($area->coordinates[0]->toJson(), true);

        $data = [
            'id' => $area->id,
            'code' => $area->code,
            'state' => $area->state,
            'city' => $area->city,
            'name' => $area->name,
            'status' => $area->status,
            'center_latitude' => $area->center_latitude,
            'center_longitude' => $area->center_longitude,
            'created_by' => $area->created_by,
            'coordinates' => ComHelper::format_coordiantes($formated_coordinates['coordinates']),
            'translations' => AreaTranslationResource::collection($area->related_translations->groupBy('language')),
        ];

        return response()->json(new AreaDetailsResource($data));
    }

    public function update(ZoneCreateRequest $request): JsonResponse
    {
        try {
            $zone = Zone::findOrFail($request->id);
            $data = $this->prepareUpdateData($request);

            foreach ($data as $column => $value) {
                // skips the translation field
                if ($column <> 'translations') {
                    $zone[$column] = $value;
                }
            }

            $zone->updated_by = auth('api')->id();
            $zone->save();

            //  Update translations
            createOrUpdateTranslation(
                $request,
                $zone->id,
                'App\Models\Zone',
                $this->translationKeys()
            );

            return $this->success(translate('messages.update_success', ['name' => 'Zone']));

        } catch (\Exception $e) {
            return $this->failed(translate('messages.update_failed', ['name' => 'Zone']),500);
        }
    }

    // Returns only the data array — used by both store() and update()
    private function prepareUpdateData($request): array
    {
        $coordinates = $request['coordinates'];
        $polygon     = [];

        foreach ($coordinates as $index => $loc) {
            if ($index === 0) {
                $lastLoc = $loc;
            }
            $polygon[] = new Point($loc['lat'], $loc['lng']);
        }

        $polygon[] = new Point($lastLoc['lat'], $lastLoc['lng']);
        $center    = calculateCenterPoint($coordinates);

        return [
            'state'            => $request->state,
            'city'             => $request->city,
            'name'             => $request->name,
            'code'             => $request->code,
            'coordinates'      => new Polygon([new LineString($polygon)]),
            'center_latitude'  => $center['center_latitude'],
            'center_longitude' => $center['center_longitude'],
            'status'           => $request->status ?? 1,
            'created_by'       => auth('api')->id(),
        ];
    }

    public function changeStatus(Request $request)
    {
        try {
            $zone = Zone::findOrFail($request->id);
            $zone->status = !$zone->status;
            $zone->save();

            return $this->success(translate('messages.status_change_success'));
        } catch (\Exception $e) {
            return $this->failed(translate('messages.update_failed', ['name' => 'Zone']),500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $zone = Zone::findOrFail($id);
            $zone->translations()->delete();
            $zone->delete();

            return $this->success(translate('messages.delete_success'));

        }catch (\Exception $exception){
            return $this->failed(translate('messages.delete_failed'));
        }

    }

    public function updateZoneSetting(StoreAreaSettingsRequest $request)
    {
        DB::beginTransaction();

        try {
            // Update or Create Zone Setting
            $zoneSetting = ZoneSetting::updateOrCreate(
                ['zone_id' => $request->zone_id],
                $request->except(['product_type_ids', 'charges'])
            );

            // Delete the existing charges for the zone setting
            ZoneSettingRangeCharge::where('zone_setting_id', $zoneSetting->id)->delete();

            // Insert new charges
            if (!empty($request->charges)) {
                $chargeData = array_map(function ($charge) use ($zoneSetting) {
                    return [
                        'zone_setting_id' => $zoneSetting->id,
                        'min_km' => $charge['min_km'],
                        'max_km' => $charge['max_km'],
                        'charge_amount' => $charge['charge_amount'],
                        'status' => $charge['status'] ?? 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $request->charges);

                // Insert the new charges in bulk
                ZoneSettingRangeCharge::insert($chargeData);
            }

            DB::commit();

            return response()->json([
                'message' => __('messages.update_success', ['name' => 'Zone Settings']),
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => __('messages.update_failed', ['name' => 'Zone Settings']),
            ], 500);
        }
    }

    public function zoneSettingsDetails($zone_id)
    {
        $zoneSettings = ZoneSetting::with(['productTypes','rangeCharges'])
            ->where('zone_id', $zone_id)
            ->first();

        if ($zoneSettings) {
            return response()->json(new AdminAreaSettingsDetailsResource($zoneSettings), 200);
        }  else {
            return response()->json(['message' => __('messages.settings_not_created_yet')], 200);
        }
    }

}
