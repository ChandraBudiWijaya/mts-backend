<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Repositories\ReportRepository; // 1. Import Repository

class ReportController extends Controller
{
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
}
