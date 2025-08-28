<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\GeofenceResource;

class GeofenceController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $geofences = Geofence::all();
        return $this->successResponse(GeofenceResource::collection($geofences), 'Data geofence berhasil diambil.');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'dwh_id' => 'required|integer|unique:geofences,dwh_id',
            'name' => 'required|string|max:255',
            'pg_group' => 'nullable|string|max:255',
            'region' => 'required|string|max:255',
            'location_code' => 'required|string|max:255',
            'area_size' => 'nullable|string|max:255',
            'coordinates' => 'required|json',
        ]);

        $geofence = Geofence::create($validatedData);

        return $this->successResponse(new GeofenceResource($geofence), 'Geofence berhasil dibuat.', 201);
    }

    public function show(Request $request, Geofence $geofence)
    {
        // Kita inject Request di sini agar bisa digunakan di dalam Resource
        return $this->successResponse(new GeofenceResource($geofence), 'Detail geofence berhasil diambil.');
    }

    public function update(Request $request, Geofence $geofence)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'pg_group' => 'nullable|string|max:255',
            'region' => 'sometimes|required|string|max:255',
            'location_code' => 'sometimes|required|string|max:255',
            'area_size' => 'nullable|string|max:255',
            'coordinates' => 'sometimes|required|json',
        ]);

        $geofence->update($validatedData);

        return $this->successResponse(new GeofenceResource($geofence), 'Geofence berhasil diperbarui.');
    }

    public function destroy(Geofence $geofence)
    {
        $geofence->delete();

        return $this->successResponse(null, 'Geofence berhasil dihapus.');
    }
}
