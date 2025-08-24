<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\ProcessDailyTrackingData;
use App\Models\MstParam; // <-- Jangan lupa import MstParam

// Ambil waktu jadwal dari database, gunakan default jika tidak ada atau error
try {
    $processTime = MstParam::where('param_key', 'schedule_process_data_time')->firstOrFail()->param_value;
    $syncTime = MstParam::where('param_key', 'schedule_sync_dwh_time')->firstOrFail()->param_value;
} catch (\Exception $e) {
    // Jika parameter tidak ditemukan di DB, gunakan waktu default
    $processTime = '01:00';
    $syncTime = '02:00';
}

// Gunakan variabel untuk mengatur jadwal
Schedule::job(new ProcessDailyTrackingData)->dailyAt($processTime);
Schedule::command('sync:dwh-locations')->dailyAt($syncTime);
