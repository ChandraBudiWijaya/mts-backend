<?php

namespace App\Repositories;

use App\Models\DailySummary;
use App\Models\Employee;
use App\Models\LocationVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class ReportRepository
{
    /**
     * Mengambil data statistik untuk komponen dashboard, mempertahankan logika asli.
     *
     * @param Request $request
     * @return array
     */
    public function getDashboardStats(Request $request): array
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Chart 1: Total User Aktif per PG
        // Definisi "aktif" adalah karyawan yang memiliki data summary pada rentang tanggal tersebut.
        $activeUsersQuery = Employee::query()
            ->whereHas('dailySummaries', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            });

        if ($request->filled('pg')) {
            $activeUsersQuery->where('plantation_group', $request->pg);
        }
        if ($request->filled('wilayah')) {
            $activeUsersQuery->where('wilayah', $request->wilayah);
        }
        $totalUserAktif = $activeUsersQuery->select('plantation_group', DB::raw('count(*) as total'))
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
            ->select('geofences.pg_group', DB::raw('count(distinct location_visits.geofence_id) as total'))
            ->groupBy('geofences.pg_group')
            ->get();

        $kunjunganPerWilayah = (clone $visitsQuery)
            ->select('geofences.region', DB::raw('count(*) as total_kunjungan'))
            ->groupBy('geofences.region')
            ->get();

        // Tabel 4: Total Coverage per Mandor
        $coverageQuery = DailySummary::whereBetween('date', [$startDate, $endDate])
            ->join('employees', 'daily_summaries.employee_id', '=', 'employees.id');

        if ($request->filled('pg')) {
            $coverageQuery->where('employees.plantation_group', $request->pg);
        }
        if ($request->filled('wilayah')) {
            $coverageQuery->where('employees.wilayah', $request->wilayah);
        }

        $coveragePerMandor = $coverageQuery->select('daily_summaries.employee_id', 'employees.name', DB::raw('SUM(total_distance_km) as total_coverage'))
            ->groupBy('daily_summaries.employee_id', 'employees.name')
            ->orderBy('total_coverage', 'desc')
            ->get();

        return [
            'totalUserAktif' => $totalUserAktif,
            'totalLokasiTerkunjungi' => $totalLokasiTerkunjungi,
            'kunjunganPerWilayah' => $kunjunganPerWilayah,
            'coveragePerMandor' => $coveragePerMandor,
        ];
    }

    /**
     * Mengambil data detail untuk tabel di halaman reporting.
     *
     * @param Request $request
     * @return Collection
     */
    public function getVisitDetails(Request $request): Collection
    {
        $query = LocationVisit::query()
            ->with(['employee:id,name', 'geofence:id,name,location_code'])
            ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        return $query->orderBy('entry_time')->get();
    }
}
