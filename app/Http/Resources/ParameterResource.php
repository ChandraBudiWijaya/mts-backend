<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParameterResource extends JsonResource
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
            'key' => $this->param_key,
            'value' => $this->param_value,
            'description' => $this->description,
            'group' => $this->group_name,
            'status' => $this->status,
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
