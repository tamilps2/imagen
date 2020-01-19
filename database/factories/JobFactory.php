<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Job;
use Faker\Generator as Faker;

$factory->define(Job::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'folder' => $faker->name . '001',
        'is_processed' => 0,
        'is_processing' => 0,
        'has_errors' => 0
    ];
});
