<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSatisJobStatisticsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('satis_job_statistics', function (Blueprint $table) {
            $table->string('uuid');
            $table->string('job');
            $table->double('avg_runtime');
            $table->integer('count')->unsigned();
            $table->timestamps();
            $table->primary(['uuid', 'job']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('satis_job_statistics');
    }
}
