<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Geofence;

class GeofenceSeeder extends Seeder
{
    /**
     * Helper function untuk mengubah string koordinat dari DWH menjadi array.
     * @param string|null $longLatString
     * @return string
     */
    private function parseCoordinates(?string $longLatString): string
    {
        if (empty($longLatString)) {
            return json_encode([]);
        }

        $points = explode(',', str_replace(' ', '', $longLatString));
        $coordinates = [];

        for ($i = 0; $i < count($points); $i += 2) {
            if (isset($points[$i]) && isset($points[$i + 1])) {
                $coordinates[] = [
                    'lng' => (float) $points[$i],
                    'lat' => (float) $points[$i + 1],
                ];
            }
        }

        return json_encode($coordinates);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama untuk menghindari duplikasi
        Geofence::truncate();

        $geofences = [
            [
                'dwh_id' => 1,
                'pg_group' => 'PG1',
                'region' => 'SAG1',
                'location_code' => '041G',
                'area_size' => '9.871913',
                'coordinates' => $this->parseCoordinates('105.2395727326475, -4.826072395907554, 105.2387283683828, -4.826078989438545, 105.238713373704, -4.827598039344274, 105.2395373129742, -4.828354718754213'),
            ],
            [
                'dwh_id' => 2,
                'pg_group' => 'PG1',
                'region' => 'SAG1',
                'location_code' => '001A',
                'area_size' => '10.5',
                'coordinates' => $this->parseCoordinates('105.1678844492829, -4.802617915216376, 105.1685347702597, -4.802160088380869, 105.1692435023914, -4.801652098118216, 105.1683840787691, -4.800398182738632'),
            ],
            [
                'dwh_id' => 3,
                'pg_group' => 'PG1',
                'region' => 'SAG1',
                'location_code' => '001B',
                'area_size' => '10.2',
                'coordinates' => $this->parseCoordinates('105.1692435023914, -4.801652098118216, 105.1699020119256, -4.801186267868846, 105.17056048146, -4.800720437619477, 105.1696001278304, -4.799580552194382'),
            ],
            [
                'dwh_id' => 4,
                'pg_group' => 'PG1',
                'region' => 'SAG1',
                'location_code' => '001C',
                'area_size' => '10.1',
                'coordinates' => $this->parseCoordinates('105.17056048146, -4.800720437619477, 105.1712189510005, -4.800254607369165, 105.171877420535, -4.799788777118854, 105.1709170669055, -4.798648891693759'),
            ],
            [
                'dwh_id' => 5,
                'pg_group' => 'PG1',
                'region' => 'SAG1',
                'location_code' => '001D',
                'area_size' => '10.3',
                'coordinates' => $this->parseCoordinates('105.171877420535, -4.799788777118854, 105.1725358900695, -4.799322946868542, 105.173194359604, -4.798857116618231, 105.1722339941905, -4.797717231193136'),
            ],
        ];

        foreach ($geofences as $geofenceData) {
            // Membuat nama yang deskriptif
            $geofenceData['name'] = "{$geofenceData['pg_group']} - {$geofenceData['region']} - {$geofenceData['location_code']}";
            Geofence::create($geofenceData);
        }
    }
}
