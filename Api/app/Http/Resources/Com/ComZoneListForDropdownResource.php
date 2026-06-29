<?php

namespace App\Http\Resources\Com;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Polygon;


class ComZoneListForDropdownResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'value' => $this->id,
            'label' => $this->name,
            'center_latitude' => $this->center_latitude,
            'center_longitude' => $this->center_longitude,
            'coordinates' => $this->coordinates ? $this->getCoordinates($this->coordinates) : null,
        ];
    }

    public function getCoordinates($polygon): array
    {
        // Assuming $this->coordinates is the Polygon object
        $polygon = $this->coordinates;

        // Initialize an empty array to hold transformed coordinates
        $transformedCoordinates = [];

        // Loop through each LineString in the Polygon's geometries collection
        foreach ($polygon->getGeometries() as $lineString) {
            if ($lineString instanceof LineString) {
                // Get the collection of points (as arrays) in the LineString
                $points = $lineString->getCoordinates(); // This returns an array of coordinates

                // Loop through each point array (each point is an array like [longitude, latitude])
                foreach ($points as $point) {
                    $transformedCoordinates[] = [
                        'lat' => $point[1], // latitude is the second element in the array
                        'lng' => $point[0], // longitude is the first element in the array
                    ];
                }
            }
        }

        return $transformedCoordinates;
    }

}
