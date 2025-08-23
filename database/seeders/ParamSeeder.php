<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParamSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('mst_params')->insert([
            'param_key' => 'sync_dwh_minutes',
            'param_value' => '1440', // Sekali sehari
            'description' => 'Interval dalam menit untuk sinkronisasi otomatis data lokasi dari DWH.',
            'group_name' => 'Sinkronisasi',
            'status' => true,
        ]);

        DB::table('mst_params')->insert([
            'param_key' => 'mandor_gps_interval_seconds',
            'param_value' => '300', // Setiap 5 menit
            'description' => 'Interval dalam detik untuk pengiriman data GPS dari aplikasi mandor.',
            'group_name' => 'Aplikasi Mobile',
            'status' => true,
        ]);
    }
}
