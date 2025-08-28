<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrackingPoint;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponse;
use App\Http\Resources\LiveTrackingResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LiveTrackingController extends Controller
{
    use ApiResponse;

    /**
     * Mengambil data lokasi terkini dari mandor.
     */
    public function index(Request $request)
    {
        $request->validate([
            'pg' => 'nullable|string',
            'wilayah' => 'nullable|string',
            'employee_id' => 'nullable|string|exists:employees,id',
        ]);

        // 1. Dapatkan ID dari tracking point terakhir untuk setiap karyawan.
        // Ini adalah cara yang efisien untuk menghindari pengambilan semua data tracking.
        $latestPointIds = TrackingPoint::select(DB::raw('MAX(id) as id'))
            // Batasi pencarian ke data dalam 24 jam terakhir untuk performa
            ->where('timestamp', '>=', Carbon::now()->subDay())
            ->groupBy('employee_id')
            ->pluck('id');

        // 2. Ambil data lengkap berdasarkan ID yang ditemukan.
        $query = TrackingPoint::with('employee')
            ->whereIn('id', $latestPointIds);

        // 3. Terapkan filter dari request
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('pg') || $request->filled('wilayah')) {
            $query->whereHas('employee', function ($q) use ($request) {
                if ($request->filled('pg')) {
                    $q->where('plantation_group', $request->pg);
                }
                if ($request->filled('wilayah')) {
                    $q->where('wilayah', $request->wilayah);
                }
            });
        }

        $latestLocations = $query->get();

        return $this->successResponse(
            LiveTrackingResource::collection($latestLocations),
            'Data live tracking berhasil diambil.'
        );
    }
}
