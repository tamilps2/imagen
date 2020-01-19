<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobPresetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_preset', function (Blueprint $table) {
            $table->bigInteger('job_id')->unsigned();
            $table->bigInteger('preset_id')->unsigned();

            $table->foreign('job_id')
                ->references('id')
                ->on('jobs');
            $table->foreign('preset_id')
                ->references('id')
                ->on('presets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_preset');
    }
}
