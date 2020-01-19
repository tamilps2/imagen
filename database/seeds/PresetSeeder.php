<?php

use Illuminate\Database\Seeder;

class PresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Preset::class, 3)->create();
    }
}
