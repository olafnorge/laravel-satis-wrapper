<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSatisConfigurationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('satis_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('homepage')->unique();
            $table->string('uuid')->unique();
            $table->longText('configuration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('satis_configurations');
    }
}
