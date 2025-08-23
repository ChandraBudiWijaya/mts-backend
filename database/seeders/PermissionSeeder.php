<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission; // Pastikan model Permission di-import

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Izin untuk Manajemen Karyawan
        Permission::create(['name' => 'View Employees', 'slug' => 'view-employees']);
        Permission::create(['name' => 'Create Employees', 'slug' => 'create-employees']);
        Permission::create(['name' => 'Edit Employees', 'slug' => 'edit-employees']);
        Permission::create(['name' => 'Delete Employees', 'slug' => 'delete-employees']);

        Permission::create(['name' => 'View Geofences', 'slug' => 'view-geofences']);
        Permission::create(['name' => 'Create Geofences', 'slug' => 'create-geofences']);
        Permission::create(['name' => 'Edit Geofences', 'slug' => 'edit-geofences']);
        Permission::create(['name' => 'Delete Geofences', 'slug' => 'delete-geofences']);
        Permission::create(['name' => 'Sync Geofences', 'slug' => 'sync-geofences']); // Untuk tombol refresh

        Permission::create(['name' => 'View Work Plans', 'slug' => 'view-work-plans']);
        Permission::create(['name' => 'Create Work Plans', 'slug' => 'create-work-plans']);
        Permission::create(['name' => 'Edit Work Plans', 'slug' => 'edit-work-plans']);
        Permission::create(['name' => 'Delete Work Plans', 'slug' => 'delete-work-plans']);
        Permission::create(['name' => 'Approve Work Plans', 'slug' => 'approve-work-plans']);
        // Nanti kita bisa tambahkan izin untuk fitur lain di sini
    }
}
