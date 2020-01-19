<?php

use Illuminate\Database\Seeder;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'password' => bcrypt('password')
        ]);

        $this->call(CompanySeeder::class);
        $this->call(PresetSeeder::class);
        $this->call(JobSeeder::class);
        $this->call(FileSeeder::class);


        \App\Job::first()->presets()->saveMany([
            \App\Preset::first(),
            \App\Preset::where('id', 2)->first()
        ]);

        \App\Job::where('id', 2)->first()->presets()->saveMany([
            \App\Preset::first()
        ]);
    }
}
