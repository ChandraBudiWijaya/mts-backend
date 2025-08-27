<?php

namespace Database\Factories;

use App\Models\TrackingPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TrackingPoint>
 */
class TrackingPointFactory extends Factory
{
    protected $model = TrackingPoint::class;

    public function definition(): array
    {
        return [
            'employee_id' => $this->faker->uuid(),
            'timestamp' => $this->faker->dateTime(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'source_id' => $this->faker->uuid(),
        ];
    }
}
