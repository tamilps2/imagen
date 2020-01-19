<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\File;
use Faker\Generator as Faker;

$factory->define(File::class, function (Faker $faker) {
    $output_dir = config('imager.job_output_dir', 'jobs/output');
    $filename = $faker->md5 . '.jpg';
    $folder = $faker->randomElement(['job001', 'job002', 'job003']);
    $storage_path = $output_dir . '/' . $folder;

    return [
        'job_id' => 1,
        'name' => $filename,
        'extension' => $faker->fileExtension,
        'folder' => $folder,
        'original_name' => $filename,
        'storage_path' => $storage_path,
        'full_path' => storage_path($storage_path),
    ];
});
