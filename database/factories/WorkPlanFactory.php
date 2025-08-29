<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use App\Models\Geofence;
use App\Models\User;
use App\Models\WorkPlan;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WorkPlan>
 */
class WorkPlanFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkPlan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'geofence_id' => Geofence::factory(),
            'date' => Carbon::today()->toDateString(),
            'activity_description' => $this->faker->sentence(),
            // 'status' => 'PENDING', // PERBAIKAN: Hapus baris ini, biarkan DB default
            'created_by' => User::factory(),
            'spk_number' => $this->faker->unique()->numerify('SPK-####'),
        ];
    }
}

