<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailySummary;
use App\Models\Employee;
use App\Models\LocationVisit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
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

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Chart 1: Total User Aktif per PG
        $activeUsersQuery = Employee::query();
        if ($request->filled('pg')) {
            $activeUsersQuery->where('plantation_group', $request->pg);
        }
        $totalUserAktif = $activeUsersQuery->select('plantation_group', \DB::raw('count(*) as total'))
            ->groupBy('plantation_group')
            ->get();

        // Chart 2 & 3: Total Lokasi Terkunjungi & Kunjungan per Wilayah
        $visitsQuery = LocationVisit::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->join('geofences', 'location_visits.geofence_id', '=', 'geofences.id');

        if ($request->filled('pg')) {
            $visitsQuery->where('geofences.pg_group', $request->pg);
        }
        if ($request->filled('wilayah')) {
            $visitsQuery->where('geofences.region', $request->wilayah);
        }

        $totalLokasiTerkunjungi = (clone $visitsQuery)
            ->select('geofences.pg_group', \DB::raw('count(distinct location_visits.geofence_id) as total'))
            ->groupBy('geofences.pg_group')
            ->get();

        $kunjunganPerWilayah = (clone $visitsQuery)
            ->select('geofences.region', \DB::raw('count(*) as total_kunjungan'))
            ->groupBy('geofences.region')
            ->get();

        // Tabel 4: Total Coverage per Mandor
        $coveragePerMandor = DailySummary::whereBetween('date', [$startDate, $endDate])
            ->join('employees', 'daily_summaries.employee_id', '=', 'employees.id')
            ->select('daily_summaries.employee_id', 'employees.name', \DB::raw('SUM(total_distance_km) as total_coverage'))
            ->groupBy('daily_summaries.employee_id', 'employees.name')
            ->orderBy('total_coverage', 'desc')
            ->get();

        return response()->json([
            'totalUserAktif' => $totalUserAktif,
            'totalLokasiTerkunjungi' => $totalLokasiTerkunjungi,
            'kunjunganPerWilayah' => $kunjunganPerWilayah,
            'coveragePerMandor' => $coveragePerMandor,
        ]);
    }

    /**
     * Menyediakan data detail untuk tabel di halaman reporting.
     */
    public function visitDetails(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'employee_id' => 'nullable|string',
        ]);

        $query = LocationVisit::query()
            ->with(['employee:id,name', 'geofence:id,name,location_code'])
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return $query->orderBy('entry_time')->get();
    }
}
