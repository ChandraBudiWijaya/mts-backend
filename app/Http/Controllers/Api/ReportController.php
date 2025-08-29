<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ReportRepository;
use App\Http\Traits\ApiResponse;
use App\Models\TrackingPoint;
use App\Http\Resources\TrackingPointResource;

class ReportController extends Controller
{
     use ApiResponse;
    protected $reportRepository;

    /**
     * Inject ReportRepository menggunakan constructor.
     * Laravel akan otomatis membuat instance-nya untuk Anda.
     */
    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
    }

    /**
     * Menyediakan data agregat untuk komponen dashboard.
     */
    public function dashboardStats(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pg' => 'nullable|string',
            'wilayah' => 'nullable|string',
        ]);

        // 2. Semua logika query sekarang ada di dalam repository. Controller jadi bersih.
        $stats = $this->reportRepository->getDashboardStats($request);

        return response()->json($stats);
    }

    /**
     * Menyediakan data detail untuk tabel di halaman reporting.
     */
    public function visitDetails(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'nullable|string', // Dibuat nullable agar bisa melihat semua karyawan
        ]);

        // 3. Logika query untuk detail kunjungan juga dipindahkan ke repository
        $visits = $this->reportRepository->getVisitDetails($request);

        return response()->json($visits);
    }

    /**
     * Mengambil koleksi data tracking point mentah untuk satu mandor pada tanggal tertentu.
     * Berguna untuk menggambar rute perjalanan di peta.
     */
    public function getTrackingPoints(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|string|exists:employees,id',
            'date' => 'required|date_format:Y-m-d',
        ]);

        $trackingPoints = TrackingPoint::where('employee_id', $validated['employee_id'])
            ->whereDate('timestamp', $validated['date'])
            ->orderBy('timestamp', 'asc')
            ->get();

        return $this->successResponse(
            TrackingPointResource::collection($trackingPoints),
            'Data titik tracking berhasil diambil.'
        );
    }
}
