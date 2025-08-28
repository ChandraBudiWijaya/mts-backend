<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Array ini mendefinisikan struktur JSON yang akan dikirim sebagai response.
        // Kita secara eksplisit memilih field mana yang akan ditampilkan.
        return [
            'employee_id' => $this->id,
            'name' => $this->name,
            'position' => $this->position,
            'phone' => $this->phone,
            'plantation_group' => $this->plantation_group,
            'wilayah' => $this->wilayah,
            'user' => [
                // 'whenLoaded' memastikan relasi 'user' hanya dimuat jika sudah di-eager load
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ],
            'roles' => $this->whenLoaded('user', function () {
                // Ambil nama role saja untuk ditampilkan
                return $this->user->roles->pluck('name');
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
