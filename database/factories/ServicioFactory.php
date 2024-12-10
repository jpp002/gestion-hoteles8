<?php

namespace Database\Factories;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->word, 
            'descripcion' => $this->faker->sentence, 
            'categoria' => $this->faker->randomElement(['Relax', 'Deportes', 'Entretenimiento', 'Servicios Básicos', 'Gastronomía']), 
        ];
    }
}