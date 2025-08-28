<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MstParam;
use App\Http\Traits\ApiResponse;

class MobileSettingsController extends Controller
{
    use ApiResponse;

    /**
     * Menyediakan parameter konfigurasi untuk aplikasi mobile.
     */
    public function getMobileSettings()
    {
        // Ambil parameter yang relevan untuk mobile dari database
        $params = MstParam::where('group_name', 'Aplikasi Mobile')
                          ->where('status', true)
                          ->pluck('param_value', 'param_key');

        // Format data agar mudah digunakan oleh aplikasi mobile
        $settings = [
            'gps_interval_seconds' => (int) ($params['mandor_gps_interval_seconds'] ?? 300),
            'work_schedule' => json_decode($params['work_schedules'] ?? '[]', true),
        ];

        return $this->successResponse($settings, 'Konfigurasi mobile berhasil diambil.');
    }
}
