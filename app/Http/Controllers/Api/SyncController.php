<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SyncController extends Controller
{
    /**
     * Menjalankan proses sinkronisasi data lokasi dari DWH.
     */
    public function runDwhSync()
    {
        try {
            // Memanggil artisan command 'sync:dwh-locations' dari dalam kode
            Artisan::call('sync:dwh-locations');

            // Mengambil output dari command jika diperlukan (opsional)
            $output = Artisan::output();

            return response()->json([
                'message' => 'Proses sinkronisasi DWH berhasil dijalankan.',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi error saat menjalankan sinkronisasi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
