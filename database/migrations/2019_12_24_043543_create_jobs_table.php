<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->text('folder');
            $table->tinyInteger('progress')->default(0);
            $table->tinyInteger('section_progress')->default(0);
            $table->string('progress_message')->nullable();
            $table->tinyInteger('is_processed')->default(0);
            $table->tinyInteger('is_processing')->default(0);
            $table->tinyInteger('has_errors')->default(0);
            $table->timestamp('processed_at')->nullable();
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
        Schema::dropIfExists('jobs');
    }
}
