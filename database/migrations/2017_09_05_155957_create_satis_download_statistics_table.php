<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSatisDownloadStatisticsTable extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('satis_download_statistics', function (Blueprint $table) {
            $table->string('package');
            $table->string('version');
            $table->integer('count')->unsigned();
            $table->timestamps();
            $table->primary(['package', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('satis_download_statistics', function (Blueprint $table) {
            Schema::dropIfExists('satis_job_statistics');
        });
    }
}
