<?php

namespace Database\Seeders;

use App\Models\Huesped;
use Illuminate\Database\Seeder;

class HuespedSeeder extends Seeder
{

    public function run()
    {
        Huesped::factory(15)->create();
    }
}
