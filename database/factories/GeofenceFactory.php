<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Geofence;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Geofence>
 */
class GeofenceFactory extends Factory
{
     /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Geofence::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Blok ' . $this->faker->unique()->word(),
            'dwh_id' => $this->faker->unique()->randomNumber(),
            'pg_group' => 'PG' . $this->faker->numberBetween(1, 5), // PERBAIKAN
            'region' => 'SAG' . $this->faker->numberBetween(1, 3), // PERBAIKAN
            'location_code' => $this->faker->unique()->bothify('####?'),
            'area_size' => $this->faker->randomFloat(2, 5, 20), // PERBAIKAN
            // Contoh koordinat poligon sederhana (persegi)
            'coordinates' => json_encode([
                ['lat' => -5.45, 'lng' => 105.26],
                ['lat' => -5.46, 'lng' => 105.26],
                ['lat' => -5.46, 'lng' => 105.27],
                ['lat' => -5.45, 'lng' => 105.27],
            ]),
        ];
    }
}
