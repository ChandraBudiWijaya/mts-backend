<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Geofence;
use Illuminate\Http\Request;

class GeofenceController extends Controller
{

    /**
     * Menampilkan daftar semua area kerja (geofence).
     */
    public function index()
    {
        return Geofence::all();
    }

    /**
     * Menyimpan area kerja baru.
     * Catatan: Pembuatan geofence utama dilakukan via sinkronisasi DWH.
     * Fungsi ini mungkin digunakan untuk kasus khusus atau tidak digunakan sama sekali.
     */
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

        // Pastikan coordinates di-decode sebelum disimpan jika inputnya string JSON
        $validatedData['coordinates'] = json_decode($validatedData['coordinates'], true);

        $geofence = Geofence::create($validatedData);

        return response()->json($geofence, 201);
    }

    /**
     * Menampilkan satu area kerja spesifik.
     */
    public function show(Geofence $geofence)
    {
        return $geofence;
    }

    /**
     * Mengupdate data area kerja.
     */
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

        if (isset($validatedData['coordinates'])) {
            $validatedData['coordinates'] = json_decode($validatedData['coordinates'], true);
        }

        $geofence->update($validatedData);

        return response()->json($geofence);
    }

    /**
     * Menghapus data area kerja.
     */
    public function destroy(Geofence $geofence)
    {
        $geofence->delete();

        return response()->json(null, 204);
    }
}
