<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari peran Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();

        // Ambil semua izin yang ada di database
        $allPermissions = Permission::all();

        // Hubungkan semua izin ke peran Super Admin
        // sync() akan otomatis menangani penambahan dan penghapusan di tabel pivot
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        // Izin untuk Kabag
        $kabagRole = Role::where('slug', 'kabag')->first();
        $kabagPermissions = Permission::whereIn('slug', [
            'view-work-plans', 'create-work-plans', 'edit-work-plans', 'delete-work-plans', 'approve-work-plans',
            'view-employees', 'view-geofences' // Kabag juga perlu melihat data master
        ])->get();
        $kabagRole->permissions()->sync($kabagPermissions->pluck('id'));

        // Izin untuk Kasie
        $kasieRole = Role::where('slug', 'kasie')->first();
        $kasiePermissions = Permission::whereIn('slug', [
            'view-work-plans', 'create-work-plans', 'edit-work-plans',
            'view-employees', 'view-geofences' // Kasie juga perlu melihat data master
        ])->get();
        $kasieRole->permissions()->sync($kasiePermissions->pluck('id'));

        // Izin untuk Mandor (hanya bisa melihat)
        $mandorRole = Role::where('slug', 'mandor')->first();
        $mandorPermissions = Permission::whereIn('slug', ['view-work-plans'])->get();
        $mandorRole->permissions()->sync($mandorPermissions->pluck('id'));
        // Di masa depan, kita bisa atur hak akses untuk peran lain di sini
        // Contoh untuk Admin:
        // $adminRole = Role::where('slug', 'admin')->first();
        // $adminPermissions = Permission::whereIn('slug', ['view-employees', 'create-employees'])->get();
        // $adminRole->permissions()->sync($adminPermissions->pluck('id'));
    }
}
