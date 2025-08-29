<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Employee;
use App\Models\Geofence;
use App\Models\WorkPlan;
use App\Providers\AuthServiceProvider;
use Carbon\Carbon;

class WorkPlanTest extends TestCase
{
    use RefreshDatabase, WithFaker; // Tambahkan WithFaker

    protected $adminUser;
    protected $mandorUserA;
    protected $mandorUserB;
    protected $geofence;

    /**
     * Menyiapkan data dasar yang dibutuhkan untuk semua tes di kelas ini.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Permissions
        $viewPermission = Permission::create(['name' => 'View Work Plans', 'slug' => 'view-work-plans']);
        $createPermission = Permission::create(['name' => 'Create Work Plans', 'slug' => 'create-work-plans']);

        // 2. Buat Roles
        $adminRole = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $adminRole->permissions()->attach([$viewPermission->id, $createPermission->id]);

        $mandorRole = Role::create(['name' => 'Mandor', 'slug' => 'mandor']);
        $mandorRole->permissions()->attach([$viewPermission->id, $createPermission->id]);

        // 3. Buat Users & Employees
        $this->adminUser = User::factory()->create();
        Employee::factory()->create(['user_id' => $this->adminUser->id]);
        $this->adminUser->roles()->attach($adminRole->id);


        $this->mandorUserA = User::factory()->create();
        // Pastikan employee terhubung dengan benar
        $employeeA = Employee::factory()->create(['user_id' => $this->mandorUserA->id]);
        $this->mandorUserA->setRelation('employee', $employeeA);
        $this->mandorUserA->roles()->attach($mandorRole->id);

        $this->mandorUserB = User::factory()->create();
        $employeeB = Employee::factory()->create(['user_id' => $this->mandorUserB->id]);
        $this->mandorUserB->setRelation('employee', $employeeB);
        $this->mandorUserB->roles()->attach($mandorRole->id);

        // 4. Buat data master lain
        $this->geofence = Geofence::factory()->create();

        // 5. Daftarkan ulang Gate
        (new AuthServiceProvider(app()))->boot();
    }


    /**
     * Tes untuk memastikan seorang mandor bisa membuat rencana kerja baru.
     */
    public function test_mandor_can_create_a_work_plan(): void
    {
        // Data untuk rencana kerja baru
        $workPlanData = [
            'employee_id' => $this->mandorUserA->employee->id,
            'geofence_id' => $this->geofence->id,
            'date' => Carbon::today()->toDateString(),
            'activity_description' => 'Inspeksi rutin area blok A1',
            'spk_number' => $this->faker->unique()->numerify('SPK-####'), // PERBAIKAN
        ];

        // Aksi: Mandor A membuat rencana kerja
        $response = $this->actingAs($this->mandorUserA)
                         ->postJson('/api/work-plans', $workPlanData);

        // Assertion
        $response->assertStatus(201)
                 ->assertJsonPath('data.activity', 'Inspeksi rutin area blok A1');

        $this->assertDatabaseHas('work_plans', [
            'employee_id' => $this->mandorUserA->employee->id,
            'activity_description' => 'Inspeksi rutin area blok A1',
        ]);
    }

    /**
     * Tes untuk memastikan mandor hanya bisa melihat rencana kerjanya sendiri.
     */
    public function test_mandor_can_only_see_their_own_work_plans(): void
    {
        // Setup: Buat rencana kerja untuk Mandor A dan Mandor B
        WorkPlan::factory()->create(['employee_id' => $this->mandorUserA->employee->id, 'activity_description' => 'Plan A']);
        WorkPlan::factory()->create(['employee_id' => $this->mandorUserB->employee->id, 'activity_description' => 'Plan B']);

        // Aksi: Login sebagai Mandor A dan ambil daftar rencana kerja
        $response = $this->actingAs($this->mandorUserA)
                         ->getJson('/api/work-plans');

        // Assertion
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data') // Harusnya hanya ada 1 rencana kerja
                 ->assertJsonPath('data.0.activity', 'Plan A') // Pastikan itu adalah milik Mandor A
                 ->assertJsonMissing(['activity_description' => 'Plan B']); // Pastikan data Mandor B tidak ada
    }

    /**
     * Tes untuk memastikan admin bisa melihat semua rencana kerja.
     */
    public function test_admin_can_see_all_work_plans(): void
    {
        // Setup: Buat rencana kerja untuk Mandor A dan Mandor B
        WorkPlan::factory()->create(['employee_id' => $this->mandorUserA->employee->id]);
        WorkPlan::factory()->create(['employee_id' => $this->mandorUserB->employee->id]);

        // Aksi: Login sebagai Admin dan ambil daftar rencana kerja
        $response = $this->actingAs($this->adminUser)
                         ->getJson('/api/work-plans');

        // Assertion
        $response->assertStatus(200)
                 ->assertJsonCount(2, 'data'); // Harusnya ada 2 rencana kerja
    }
}

