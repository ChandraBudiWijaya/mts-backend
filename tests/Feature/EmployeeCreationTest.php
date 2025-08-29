<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Employee;
use App\Providers\AuthServiceProvider; // 1. Import AuthServiceProvider

class EmployeeCreationTest extends TestCase
{
    // Trait ini akan secara otomatis mereset database untuk setiap tes
    use RefreshDatabase;

    /**
     * Tes untuk memastikan seorang admin bisa membuat karyawan baru.
     *
     * @return void
     */
    public function test_admin_can_create_a_new_employee(): void
    {
        // 1. PANGGUNG (SETUP)
        // Kita siapkan semua data yang dibutuhkan untuk tes ini.

        // Buat permission yang dibutuhkan
        $permission = Permission::create([
            'name' => 'Create Employees',
            'slug' => 'create-employees'
        ]);

        // Buat role 'Admin'
        $adminRole = Role::create([
            'name' => 'Admin',
            'slug' => 'admin'
        ]);
        $adminRole->permissions()->attach($permission->id);

        // Buat user yang akan bertindak sebagai admin
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole->id);

        // 2. DAFTARKAN ULANG GATE OTOMATIS
        // Ini penting karena AuthServiceProvider mendaftarkan gate saat aplikasi boot,
        // sebelum RefreshDatabase membuat permission baru di database tes.
        (new AuthServiceProvider(app()))->boot();


        // Siapkan data untuk karyawan baru yang akan kita buat
        $newEmployeeData = [
            'id' => '12345', // PERBAIKAN: Ubah ID menjadi string
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $adminRole->id, // Berikan role yang sama untuk contoh ini
            'position' => 'Staff IT',
            'plantation_group' => 'PG1',
            'wilayah' => 'W01',
            'phone' => '081234567890',
        ];


        // 3. AKSI (ACTION)
        // Kita simulasikan admin tersebut mengirim request API untuk membuat karyawan baru.

        $response = $this->actingAs($adminUser) // Bertindak sebagai adminUser
                         ->postJson('/api/employees', $newEmployeeData);

        // -- CARA MELIHAT OUTPUT --
        // Hapus komentar di bawah ini untuk melihat isi response JSON di terminal
        // dd($response->json());


        // 4. PENEGASAN (ASSERTION)
        // Kita periksa apakah hasilnya sesuai dengan yang kita harapkan.

        // Pastikan response sukses (HTTP Status 201 - Created)
        $response->assertStatus(201);

        // Pastikan data karyawan baru benar-benar ada di database
        $this->assertDatabaseHas('employees', [
            'id' => '12345',
            'name' => 'Budi Santoso',
        ]);

        // Pastikan user baru juga ada di database
        $this->assertDatabaseHas('users', [
            'email' => 'budi.santoso@example.com',
        ]);

        // Pastikan response JSON memiliki data karyawan yang baru dibuat
        // PERBAIKAN: Sesuaikan dengan output resource yang sebenarnya
        $response->assertJson([
            'success' => true,
            'data' => [
                'employee_id' => '12345', // Cek 'employee_id' tanpa prefix
                'name' => 'Budi Santoso',
            ]
        ]);
    }

    /**
     * Tes baru untuk memastikan validasi email duplikat berfungsi.
     *
     * @return void
     */
    public function test_creating_employee_fails_if_email_already_exists(): void
    {
        // 1. SETUP
        // Buat user yang emailnya akan kita coba duplikasi
        User::factory()->create(['email' => 'test@example.com']);

        // Siapkan user admin yang akan melakukan aksi
        $permission = Permission::create(['name' => 'Create Employees', 'slug' => 'create-employees']);
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $adminRole->permissions()->attach($permission->id);
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach($adminRole->id);
        (new AuthServiceProvider(app()))->boot();

        // Siapkan data karyawan baru dengan email yang sudah ada
        $duplicateEmployeeData = [
            'id' => '54321',
            'name' => 'Andi',
            'email' => 'test@example.com', // Email yang sama dengan user di atas
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $adminRole->id,
            'position' => 'Staff Lapangan',
            'plantation_group' => 'PG2',
            'wilayah' => 'W02',
            'phone' => '089876543210',
        ];

        // 2. ACTION
        $response = $this->actingAs($adminUser)
                         ->postJson('/api/employees', $duplicateEmployeeData);

        // dd($response->json());

        // 3. ASSERTION
        // Pastikan response adalah error validasi (HTTP 422)
        $response->assertStatus(422);

        // Pastikan response JSON berisi pesan error yang spesifik untuk email
        $response->assertJsonValidationErrors(['email']);
    }

    /**
     * Tes baru untuk memastikan user tanpa izin tidak bisa membuat karyawan.
     *
     * @return void
     */
    public function test_user_without_permission_cannot_create_employee(): void
    {
        // 1. SETUP
        // Buat role "Mandor" yang TIDAK memiliki permission 'create-employees'
        $mandorRole = Role::create(['name' => 'Mandor', 'slug' => 'mandor']);

        // Buat user dengan role Mandor
        $mandorUser = User::factory()->create();
        $mandorUser->roles()->attach($mandorRole->id);
        (new AuthServiceProvider(app()))->boot();

        // Siapkan data karyawan baru
        $newEmployeeData = [
            'id' => '98765',
            'name' => 'Joko',
            'email' => 'joko@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => $mandorRole->id,
            'position' => 'Mandor Lapangan',
            'plantation_group' => 'PG3',
            'wilayah' => 'W03',
            'phone' => '08111222333',
        ];

        // 2. ACTION
        // Simulasikan user mandor mencoba membuat karyawan baru
        $response = $this->actingAs($mandorUser)
                         ->postJson('/api/employees', $newEmployeeData);

        // dd($response->json());

        // 3. ASSERTION
        // Pastikan response adalah error 403 Forbidden (Dilarang)
        $response->assertForbidden();

        // Pastikan data karyawan baru TIDAK ada di database
        $this->assertDatabaseMissing('employees', [
            'id' => '98765',
        ]);
    }
}

