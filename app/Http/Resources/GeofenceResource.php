<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeofenceResource extends JsonResource
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
            'dwh_id' => $this->dwh_id,
            'name' => $this->name,
            'plantation_group' => $this->pg_group,
            'region' => $this->region,
            'location_code' => $this->location_code,
            'area_size' => $this->area_size,
            // Hanya tampilkan koordinat jika route-nya adalah 'geofences.show'
            'coordinates' => $this->when($request->routeIs('geofences.show'), $this->coordinates),
        ];
    }
}
