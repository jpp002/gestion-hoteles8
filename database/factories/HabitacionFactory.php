<?php

namespace Database\Factories;

use App\Models\Habitacion;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

class HabitacionFactory extends Factory
{
    protected $model = Habitacion::class;

    public function definition()
    {
        return [
            'numero' => $this->faker->unique()->numberBetween(1, 500),
            'tipo' => $this->faker->randomElement(['simple', 'doble', 'suite', 'familiar', 'deluxe']),
            'precioNoche' => $this->faker->randomFloat(2, 50, 500),
            'hotel_id' => Hotel::inRandomOrder()->first()->id, // Relación con Hotel
        ];
    }
}
