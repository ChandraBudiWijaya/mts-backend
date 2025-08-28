<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveTrackingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // Data dari tabel Employee (melalui relasi)
            'employee_id' => $this->employee->id,
            'name' => $this->employee->name,
            'position' => $this->employee->position,
            'plantation_group' => $this->employee->plantation_group,
            'wilayah' => $this->employee->wilayah,
            'photo_url' => $this->employee->photo_url,

            // Data dari tabel TrackingPoint
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'last_update' => $this->timestamp->format('Y-m-d H:i:s'),
        ];
    }
}
