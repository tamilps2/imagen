<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Preset;
use Faker\Generator as Faker;

$factory->define(Preset::class, function (Faker $faker) {
    $sm_length = $faker->numberBetween(600, 1000);
    $lg_length = $faker->numberBetween(1200, 1080);

    return [
        'name' => $faker->sentence,
        'filename_pattern' => $faker->randomElement(['original', 'replace', 'append', 'prepend']),
        'filename' => 'image',

        'generate_small_image' => true,
        'sm_width' => 600,
        'sm_height' => 600,
        'sm_watermark' => true,
        'sm_should_upload' => true,
        'sm_company_id' => 1,
        'sm_wm_position' => $faker->randomElement(Preset::availablePositions()),
        'sm_wm_unit' => $faker->randomElement(Preset::availableUnits()),
        'sm_wm_x_axis' => 3,
        'sm_wm_y_axis' => 3,

        'generate_large_image' => true,
        'lg_width' => 1920,
        'lg_height' => 1080,
        'lg_watermark' => true,
        'lg_should_upload' => true,
        'lg_company_id' => 1,
        'lg_wm_position' => $faker->randomElement(Preset::availablePositions()),
        'lg_wm_unit' => $faker->randomElement(Preset::availableUnits()),
        'lg_wm_x_axis' => 3,
        'lg_wm_y_axis' => 3,

        'generate_gif' => true,
        'gif_width' => 500,
        'gif_height' => 500,
        'gif_watermark' => true,
        'gif_should_upload' => true,
        'gif_company_id' => 1,
        'gif_wm_position' => $faker->randomElement(Preset::availablePositions()),
        'gif_wm_unit' => $faker->randomElement(Preset::availableUnits()),
        'gif_wm_x_axis' => 3,
        'gif_wm_y_axis' => 3,
        'gif_interval' => 3,

        'generate_video' => true,
        'video_width' => 1920,
        'video_height' => 1080,
        'video_watermark' => true,
        'video_should_upload' => true,
        'video_company_id' => 1,
        'video_wm_position' => $faker->randomElement(Preset::availablePositions()),
        'video_wm_unit' => $faker->randomElement(Preset::availableUnits()),
        'video_wm_x_axis' => 3,
        'video_wm_y_axis' => 3,
        'video_fps' => 24,
        'upload_to_youtube' => false,
    ];
});
