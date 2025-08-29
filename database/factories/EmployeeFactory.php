<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Employee;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numerify('MDR#####'),
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'position' => 'Mandor',
            'plantation_group' => 'PG' . $this->faker->numberBetween(1, 5),
            'wilayah' => 'W' . $this->faker->numberBetween(1, 3),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
