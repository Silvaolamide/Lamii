<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Technology','Business','AI','Music','Movies','Photography','Sports','Gaming','Fashion','Travel','Fitness','Food','Art','Books','Networking'] as $name) {
            Interest::firstOrCreate(['name' => $name], ['slug' => str($name)->slug()]);
        }
    }
}
