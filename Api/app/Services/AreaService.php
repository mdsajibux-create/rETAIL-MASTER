<?php

namespace App\Services;

use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;

class AreaService
{

    public function prepareAddData(object $request): array
    {
        $coordinates = $request['coordinates'];
        $location = '';
        $coordinates = $request['coordinates'];

        foreach ($coordinates as $index => $loc) {
            if ($index == 0) {
                $lastLoc = $loc;
            }
            $polygon[] = new Point($loc['lat'], $loc['lng']);
        }

        $polygon[] = new Point($lastLoc['lat'], $lastLoc['lng']);
        $center = calculateCenterPoint($request['coordinates']);

        return [
            'state' => $request->state,
            'city' => $request->city,
            'name' => $request->name,
            'code' => $request->code,
            'coordinates' => new Polygon([new LineString($polygon)]),
            'center_latitude' => $center['center_latitude'],
            'center_longitude' => $center['center_longitude'],
            'status' => $request->status ?? 1,
            'created_by' => auth('api')->id(),
        ];
    }


}
