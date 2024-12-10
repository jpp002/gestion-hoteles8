<?php

namespace Database\Seeders;

use App\Models\Servicio;
use Illuminate\Database\Seeder;

class ServicioSeeder extends Seeder
{

    public function run()
    {
        Servicio::factory(5)->create();
    }
}
