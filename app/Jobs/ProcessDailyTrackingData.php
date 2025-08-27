<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Kreait\Firebase\Contract\Firestore;
use App\Models\Employee;
use App\Models\Geofence;
use App\Models\DailySummary;
use App\Models\LocationVisit;
use App\Models\TrackingPoint;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessDailyTrackingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dateToProcess;

    /**
     * Create a new job instance.
     */
    public function __construct(Carbon $dateToProcess = null)
    {
        // Jika tidak ada tanggal spesifik, proses data untuk hari kemarin.
        $this->dateToProcess = $dateToProcess ?? Carbon::yesterday();
    }

    /**
     * Execute the job.
     */
    public function handle(Firestore $firestore): void
    {
        Log::info("Memulai job ProcessDailyTrackingData untuk tanggal: " . $this->dateToProcess->toDateString());

        $db = $firestore->database();
        $allEmployees = Employee::all();
        $allGeofences = Geofence::all(); // Ambil semua geofence sekali saja untuk efisiensi

        foreach ($allEmployees as $employee) {
            $dateStr = $this->dateToProcess->format('Y-m-d');
            $collectionPath = "tracking/{$employee->id}/{$dateStr}";

            // 1. EXTRACT: Ambil semua data GPS untuk karyawan ini pada hari tersebut dari Firestore
            $documents = $db->collection($collectionPath)->orderBy('timestamp')->documents();

            $trackingPoints = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $data = $document->data();
                    // Pastikan data memiliki format yang benar
                    if (isset($data['latitude'], $data['longitude'], $data['timestamp'])) {
                        $timestamp = Carbon::parse($data['timestamp']);

                        // Simpan titik tracking mentah sebelum proses transformasi
                        TrackingPoint::updateOrCreate(
                            ['source_id' => $document->id()],
                            [
                                'employee_id' => $employee->id,
                                'timestamp' => $timestamp,
                                'latitude' => $data['latitude'],
                                'longitude' => $data['longitude'],
                            ]
                        );

                        // Kumpulkan titik untuk proses transformasi berikutnya
                        $trackingPoints[] = [
                            'lat' => $data['latitude'],
                            'lng' => $data['longitude'],
                            'timestamp' => $timestamp,
                        ];
                    }
                }
            }

            if (empty($trackingPoints)) {
                Log::info("Tidak ada data tracking untuk karyawan {$employee->id} pada {$dateStr}.");
                continue; // Lanjut ke karyawan berikutnya
            }

            // 2. TRANSFORM: Olah data GPS menjadi informasi yang berguna
            $totalDurationMinutes = $this->calculateTotalDuration($trackingPoints);
            $totalDistanceKm = $this->calculateTotalDistance($trackingPoints);
            $visitData = $this->analyzeVisits($trackingPoints, $allGeofences);

            // 3. LOAD: Simpan hasil olahan ke database PostgreSQL
            $this->saveSummary($employee, $dateStr, $totalDurationMinutes, $totalDistanceKm, $visitData['total_minutes_inside']);
            $this->saveLocationVisits($employee, $dateStr, $visitData['visits']);

            Log::info("Berhasil memproses data untuk karyawan {$employee->id} pada {$dateStr}.");
        }

        Log::info("Selesai menjalankan job ProcessDailyTrackingData.");
    }

    // --- Fungsi-fungsi Helper untuk Kalkulasi ---

    private function calculateTotalDuration(array $points): int
    {
        if (count($points) < 2) return 0;
        $startTime = $points[0]['timestamp'];
        $endTime = end($points)['timestamp'];
        return $startTime->diffInMinutes($endTime);
    }

    private function calculateTotalDistance(array $points): float
    {
        $totalDistance = 0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $totalDistance += $this->haversineDistance(
                $points[$i]['lat'], $points[$i]['lng'],
                $points[$i+1]['lat'], $points[$i+1]['lng']
            );
        }
        return round($totalDistance, 2);
    }

    // Fungsi untuk menganalisis kunjungan (ini adalah logika inti yang disederhanakan)
    private function analyzeVisits(array $points, $geofences): array
    {
        // Logika untuk mendeteksi kapan mandor masuk dan keluar dari setiap geofence
        // dan menghitung total durasi di dalamnya.
        // Untuk saat ini, kita akan menggunakan data dummy.
        // Implementasi sebenarnya memerlukan algoritma point-in-polygon.

        // Contoh data hasil analisis (dummy)
        return [
            'total_minutes_inside' => rand(100, 400),
            'visits' => [
                [
                    'geofence_id' => 1,
                    'entry_time' => $points[0]['timestamp']->copy()->addMinutes(10),
                    'exit_time' => $points[0]['timestamp']->copy()->addMinutes(70),
                    'visit_duration_minutes' => 60,
                ],
                [
                    'geofence_id' => 2,
                    'entry_time' => $points[0]['timestamp']->copy()->addMinutes(90),
                    'exit_time' => $points[0]['timestamp']->copy()->addMinutes(150),
                    'visit_duration_minutes' => 60,
                ]
            ]
        ];
    }

    private function saveSummary($employee, $date, $totalDuration, $totalDistance, $minutesInside)
    {
        DailySummary::updateOrCreate(
            ['employee_id' => $employee->id, 'date' => $date],
            [
                'total_work_minutes' => $totalDuration,
                'total_distance_km' => $totalDistance,
                'total_outside_area_minutes' => $totalDuration - $minutesInside,
                'last_update' => Carbon::now(),
            ]
        );
    }

    private function saveLocationVisits($employee, $date, array $visits)
    {
        // Hapus data kunjungan lama untuk hari itu untuk menghindari duplikasi
        LocationVisit::where('employee_id', $employee->id)->where('date', $date)->delete();

        foreach ($visits as $visit) {
            LocationVisit::create([
                'date' => $date,
                'employee_id' => $employee->id,
                'geofence_id' => $visit['geofence_id'],
                'entry_time' => $visit['entry_time'],
                'exit_time' => $visit['exit_time'],
                'visit_duration_minutes' => $visit['visit_duration_minutes'],
            ]);
        }
    }

    // Rumus Haversine untuk menghitung jarak antara dua titik koordinat
    private function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // Radius bumi dalam kilometer
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}
