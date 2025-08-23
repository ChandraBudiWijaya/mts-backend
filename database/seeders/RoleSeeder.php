<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Super Admin', 'slug' => 'super-admin']);
        Role::create(['name' => 'Admin', 'slug' => 'admin']);
        Role::create(['name' => 'Mandor', 'slug' => 'mandor']);
        Role::create(['name' => 'Kasie', 'slug' => 'kasie']);
        Role::create(['name' => 'Kabag', 'slug' => 'kabag']);
    }
}
