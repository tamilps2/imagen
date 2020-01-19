<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePresetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('presets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');

            $table->text('filename_pattern')->nullable();
            $table->text('filename')->nullable();
            // Small image
            $table->boolean('generate_small_image')->default(0);
            $table->integer('sm_width')->default(600);
            $table->integer('sm_height')->default(600);
            $table->boolean('sm_watermark')->default(0);
            $table->bigInteger('sm_company_id')->nullable();
            $table->text('sm_wm_position')->nullable();
            $table->text('sm_wm_unit')->nullable();
            $table->text('sm_wm_x_axis')->nullable();
            $table->text('sm_wm_y_axis')->nullable();
            $table->boolean('sm_should_upload')->default(0);
            // large image
            $table->boolean('generate_large_image')->default(0);
            $table->integer('lg_width')->default(1200);
            $table->integer('lg_height')->default(1200);
            $table->boolean('lg_watermark')->default(0);
            $table->bigInteger('lg_company_id')->nullable();
            $table->text('lg_wm_position')->nullable();
            $table->text('lg_wm_unit')->nullable();
            $table->text('lg_wm_x_axis')->nullable();
            $table->text('lg_wm_y_axis')->nullable();
            $table->boolean('lg_should_upload')->default(0);
            // Gif generation
            $table->boolean('generate_gif')->default(0);
            $table->integer('gif_width')->default(500);
            $table->integer('gif_height')->default(500);
            $table->integer('gif_interval')->nullable()->default(3);
            $table->boolean('gif_watermark')->default(0);
            $table->bigInteger('gif_company_id')->nullable();
            $table->text('gif_wm_position')->nullable();
            $table->text('gif_wm_unit')->nullable();
            $table->text('gif_wm_x_axis')->nullable();
            $table->text('gif_wm_y_axis')->nullable();
            $table->boolean('gif_should_upload')->default(0);
            // Video
            $table->boolean('generate_video')->default(0);
            $table->integer('video_width')->default(1920);
            $table->integer('video_height')->default(1080);
            $table->integer('video_fps')->nullable()->default(24);
            $table->boolean('video_watermark')->default(0);
            $table->bigInteger('video_company_id')->nullable();
            $table->text('video_wm_position')->nullable();
            $table->text('video_wm_unit')->nullable();
            $table->text('video_wm_x_axis')->nullable();
            $table->text('video_wm_y_axis')->nullable();
            $table->boolean('video_should_upload')->default(0);
            $table->boolean('upload_to_youtube')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('presets');
    }
}
