<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat User Super Admin
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'), // Ganti dengan password yang aman
        ]);
        $superAdmin->roles()->attach($superAdminRole);

        // 2. Buat User Admin/Manager
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminUser = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
            ]);
            $adminUser->roles()->attach($adminRole);
        }

        // 3. Buat User Mandor
        $mandorRole = Role::where('slug', 'mandor')->first();
        if ($mandorRole) {
            $mandorUser = User::create([
                'name' => 'Budi Mandor',
                'email' => 'budi.mandor@example.com',
                'password' => Hash::make('password'),
            ]);
            $mandorUser->roles()->attach($mandorRole);

            // Buat juga entri di tabel employees untuk mandor ini
            Employee::create([
                'id' => 'MDR001',
                'name' => 'Budi Mandor',
                'user_id' => $mandorUser->id,
                'position' => 'Mandor Lapangan',
                'plantation_group' => 'PG1',
                'wilayah' => 'WIL1',
                'phone' => '081234567890',
            ]);
        }
    }
}
