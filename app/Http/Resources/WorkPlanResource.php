<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkPlanResource extends JsonResource
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
            'date' => $this->date,
            'spk_number' => $this->spk_number,
            'activity' => $this->activity_description,
            'status' => $this->status,

            // Memuat data relasi hanya jika sudah di-load (mencegah N+1 problem)
            'employee' => $this->whenLoaded('employee', [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
            ]),

            'geofence' => $this->whenLoaded('geofence', [
                'id' => $this->geofence->id,
                'name' => $this->geofence->name,
                'location_code' => $this->geofence->location_code,
            ]),

            'created_by' => $this->whenLoaded('creator', $this->creator->name),
            'approved_by' => $this->whenLoaded('approver', optional($this->approver)->name), // Gunakan optional jika approver bisa null

            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
