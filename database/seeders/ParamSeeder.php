<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Menghapus data lama agar tidak duplikat
        DB::table('mst_params')->truncate();

        DB::table('mst_params')->insert([
            [
                'param_key' => 'sync_dwh_minutes',
                'param_value' => '1440', // Sekali sehari
                'description' => 'Interval dalam menit untuk sinkronisasi otomatis data lokasi dari DWH.',
                'group_name' => 'Sinkronisasi',
                'status' => true,
            ],
            [
                'param_key' => 'mandor_gps_interval_seconds',
                'param_value' => '300', // Setiap 5 menit
                'description' => 'Interval dalam detik untuk pengiriman data GPS dari aplikasi mandor.',
                'group_name' => 'Aplikasi Mobile',
                'status' => true,
            ],
            [
                'param_key' => 'schedule_process_data_time',
                'param_value' => '01:00', // Waktu default untuk job pengolah data
                'description' => 'Waktu (HH:MM) untuk menjalankan job pengolah data harian.',
                'group_name' => 'Penjadwalan',
                'status' => true,
            ],
            [
                'param_key' => 'schedule_sync_dwh_time',
                'param_value' => '02:00', // Waktu default untuk sinkronisasi DWH
                'description' => 'Waktu (HH:MM) untuk menjalankan sinkronisasi DWH harian.',
                'group_name' => 'Penjadwalan',
                'status' => true,
            ]
        ]);
    }
}
