<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPasswordColumnFromSatisConfigurationsTable extends Migration {


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('satis_configurations', function (Blueprint $table) {
            $table->dropColumn(['password']);
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('satis_configurations', function (Blueprint $table) {
            $table->string('password')->default('')->after('homepage');
        });
    }
}
