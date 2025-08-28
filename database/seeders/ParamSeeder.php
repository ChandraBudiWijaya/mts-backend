<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParamSeeder extends Seeder
{
    public function run(): void
    {
        // Hapus data lama agar tidak duplikat saat seeder dijalankan ulang
        DB::table('mst_params')->whereIn('param_key', [
            'sync_dwh_minutes',
            'mandor_gps_interval_seconds',
            'schedule_process_data_time',
            'schedule_sync_dwh_time',
            'work_schedules'
        ])->delete();


        // Definisikan jadwal kerja yang baru
        $workScheduleValue = json_encode([
            '1-4' => [ // Senin - Kamis
                ['start' => [7, 45], 'end' => [12, 0]],
                ['start' => [13, 0], 'end' => [16, 0]],
            ],
            '5' => [ // Jumat
                ['start' => [7, 45], 'end' => [11, 30]],
                ['start' => [13, 30], 'end' => [16, 0]],
            ],
            '6' => [ // Sabtu (Setengah Hari)
                ['start' => [7, 45], 'end' => [12, 0]],
            ],
            '7' => [], // Minggu (libur)
        ]);

        DB::table('mst_params')->insert([
            [
                'param_key' => 'sync_dwh_minutes',
                'param_value' => '1440',
                'description' => 'Interval dalam menit untuk sinkronisasi otomatis data lokasi dari DWH.',
                'group_name' => 'Sinkronisasi',
                'status' => true,
            ],
            [
                'param_key' => 'mandor_gps_interval_seconds',
                'param_value' => '300',
                'description' => 'Interval dalam detik untuk pengiriman data GPS dari aplikasi mandor.',
                'group_name' => 'Aplikasi Mobile',
                'status' => true,
            ],
            [
                'param_key' => 'schedule_process_data_time',
                'param_value' => '01:00',
                'description' => 'Waktu (HH:MM) untuk menjalankan job pengolah data harian.',
                'group_name' => 'Penjadwalan',
                'status' => true,
            ],
            [
                'param_key' => 'schedule_sync_dwh_time',
                'param_value' => '02:00',
                'description' => 'Waktu (HH:MM) untuk menjalankan sinkronisasi DWH harian.',
                'group_name' => 'Penjadwalan',
                'status' => true,
            ],
            [
                'param_key' => 'work_schedules',
                'param_value' => $workScheduleValue,
                'description' => 'Jadwal jam kerja mandor dalam format JSON.',
                'group_name' => 'Aplikasi Mobile',
                'status' => true,
            ]
        ]);
    }
}
