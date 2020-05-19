<?php

use Illuminate\Database\Migrations\Migration;

class TruncateJobStatistics extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::table('satis_job_statistics')->truncate();
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // no way back, see up method
    }
}
