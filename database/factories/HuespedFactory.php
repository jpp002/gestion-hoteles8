<?php

namespace Database\Factories;

use App\Models\Huesped;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Huesped>
 */
class HuespedFactory extends Factory
{
    protected $model = Huesped::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name,
            'apellido'=> $this->faker->lastName,
            'dniPasaporte' => $this->faker->regexify('[0-9]{8}[A-Z]')
        ];
    }
}