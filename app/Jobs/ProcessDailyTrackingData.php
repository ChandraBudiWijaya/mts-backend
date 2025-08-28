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
use Illuminate\Support\Collection;

class ProcessDailyTrackingData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $dateToProcess;

    public function __construct(Carbon $dateToProcess = null)
    {
        $this->dateToProcess = $dateToProcess ?? Carbon::yesterday();
    }

    public function handle(Firestore $firestore): void
    {
        Log::info("Memulai job ProcessDailyTrackingData untuk tanggal: " . $this->dateToProcess->toDateString());

        $db = $firestore->database();
        $allEmployees = Employee::all();
        $allGeofences = Geofence::all();

        foreach ($allEmployees as $employee) {
            $dateStr = $this->dateToProcess->format('Y-m-d');
            $collectionPath = "tracking/{$employee->id}/{$dateStr}";

            $documents = $db->collection($collectionPath)->orderBy('timestamp')->documents();

            $rawTrackingPoints = [];
            foreach ($documents as $document) {
                if ($document->exists()) {
                    $data = $document->data();
                    if (isset($data['latitude'], $data['longitude'], $data['timestamp'])) {
                        $timestamp = Carbon::parse($data['timestamp']);

                        TrackingPoint::updateOrCreate(
                            ['source_id' => $document->id()],
                            [
                                'employee_id' => $employee->id,
                                'timestamp' => $timestamp,
                                'latitude' => $data['latitude'],
                                'longitude' => $data['longitude'],
                            ]
                        );

                        $rawTrackingPoints[] = [
                            'lat' => $data['latitude'],
                            'lng' => $data['longitude'],
                            'timestamp' => $timestamp,
                        ];
                    }
                }
            }

            if (empty($rawTrackingPoints)) {
                Log::info("Tidak ada data tracking untuk karyawan {$employee->id} pada {$dateStr}.");
                continue;
            }

            $trackingPointsCollection = collect($rawTrackingPoints);

            $totalDurationMinutes = $this->calculateTotalDuration($rawTrackingPoints);
            $totalDistanceKm = $this->calculateTotalDistance($rawTrackingPoints);
            $visitData = $this->analyzeVisits($trackingPointsCollection, $allGeofences);

            // Simpan summary
            $summary = DailySummary::updateOrCreate(
                ['employee_id' => $employee->id, 'date' => $dateStr],
                [
                    'total_work_minutes' => $totalDurationMinutes,
                    'total_distance_km' => $totalDistanceKm,
                    'total_outside_area_minutes' => max(0, $totalDurationMinutes - $visitData['total_minutes_inside']),
                    'last_update' => Carbon::now(),
                ]
            );

            // Simpan detail kunjungan
            $this->saveLocationVisits($summary, $visitData['visits']);

            Log::info("Berhasil memproses data untuk karyawan {$employee->id} pada {$dateStr}.");
        }

        Log::info("Selesai menjalankan job ProcessDailyTrackingData.");
    }

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

    private function analyzeVisits(Collection $points, Collection $geofences): array
    {
        $visits = [];
        $activeVisits = [];
        $totalMinutesInside = 0;

        foreach ($points as $point) {
            $pointCoords = ['lat' => $point['lat'], 'lng' => $point['lng']];

            foreach ($geofences as $geofence) {
                // Koordinat disimpan sebagai JSON, jadi kita decode.
                $polygon = json_decode($geofence->coordinates, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($polygon)) {
                    continue; // Lewati geofence jika datanya tidak valid
                }

                $isInside = $this->isPointInPolygon($pointCoords, $polygon);
                $activeVisitKey = 'geofence_' . $geofence->id;

                if ($isInside) {
                    if (!isset($activeVisits[$activeVisitKey])) {
                        $activeVisits[$activeVisitKey] = [
                            'geofence_id' => $geofence->id,
                            'entry_time' => $point['timestamp'],
                            'last_seen_time' => $point['timestamp'],
                        ];
                    } else {
                        $activeVisits[$activeVisitKey]['last_seen_time'] = $point['timestamp'];
                    }
                } else {
                    if (isset($activeVisits[$activeVisitKey])) {
                        $visitData = $activeVisits[$activeVisitKey];
                        $durationMinutes = $visitData['entry_time']->diffInMinutes($visitData['last_seen_time']);

                        if ($durationMinutes > 0) {
                             $visits[] = [
                                'geofence_id' => $visitData['geofence_id'],
                                'entry_time' => $visitData['entry_time'],
                                'exit_time' => $visitData['last_seen_time'],
                                'visit_duration_minutes' => $durationMinutes,
                            ];
                            $totalMinutesInside += $durationMinutes;
                        }
                        unset($activeVisits[$activeVisitKey]);
                    }
                }
            }
        }

        foreach ($activeVisits as $visitData) {
            $durationMinutes = $visitData['entry_time']->diffInMinutes($visitData['last_seen_time']);
             if ($durationMinutes > 0) {
                $visits[] = [
                    'geofence_id' => $visitData['geofence_id'],
                    'entry_time' => $visitData['entry_time'],
                    'exit_time' => $visitData['last_seen_time'],
                    'visit_duration_minutes' => $durationMinutes,
                ];
                $totalMinutesInside += $durationMinutes;
            }
        }

        return [
            'visits' => $visits,
            'total_minutes_inside' => $totalMinutesInside,
        ];
    }

    private function saveLocationVisits(DailySummary $summary, array $visits)
    {
        // Hapus kunjungan lama untuk ringkasan ini untuk menghindari duplikasi
        LocationVisit::where('daily_summary_id', $summary->id)->delete();

        foreach ($visits as $visit) {
            LocationVisit::create([
                'daily_summary_id' => $summary->id,
                'geofence_id' => $visit['geofence_id'],
                'check_in_time' => $visit['entry_time'],
                'check_out_time' => $visit['exit_time'],
                'duration_seconds' => $visit['entry_time']->diffInSeconds($visit['exit_time']),
            ]);
        }
    }

    private function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    private function isPointInPolygon(array $point, array $polygon): bool
    {
        $inside = false;
        $x = $point['lng'];
        $y = $point['lat'];
        $vertexCount = count($polygon);

        if ($vertexCount < 3) {
            return false;
        }

        // Pastikan format koordinat di dalam poligon adalah ['lat' => ..., 'lng' => ...]
        // Jika formatnya [lng, lat], sesuaikan di sini.
        // Asumsi saat ini adalah format ['lat' => ..., 'lng' => ...]

        $j = $vertexCount - 1;
        for ($i = 0; $i < $vertexCount; $j = $i++) {
            // Periksa apakah format array polygon memiliki key 'lat' dan 'lng'
            if (!isset($polygon[$i]['lat']) || !isset($polygon[$i]['lng']) || !isset($polygon[$j]['lat']) || !isset($polygon[$j]['lng'])) {
                // Jika formatnya hanya [lng, lat]
                 $xi = $polygon[$i][0]; // lng
                 $yi = $polygon[$i][1]; // lat
                 $xj = $polygon[$j][0]; // lng
                 $yj = $polygon[$j][1]; // lat
            } else {
                // Jika formatnya ['lat' => ..., 'lng' => ...]
                 $xi = $polygon[$i]['lng'];
                 $yi = $polygon[$i]['lat'];
                 $xj = $polygon[$j]['lng'];
                 $yj = $polygon[$j]['lat'];
            }

            $intersect = (($yi > $y) !== ($yj > $y))
                && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi);
            if ($intersect) {
                $inside = !$inside;
            }
        }

        return $inside;
    }
}
