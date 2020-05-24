<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePrimarykeyFromIdToUuidAtSatisConfigurationsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        // rename current table in order to populate the data back to the new structure
        Schema::rename('satis_configurations', 'satis_configurations_tmp');

        // create new structure
        Schema::create('satis_configurations', function (Blueprint $table) {
            $table->string('uuid');
            $table->string('name')->unique();
            $table->string('homepage')->unique();
            $table->string('crontab')->nullable()->default('');
            $table->longText('configuration');
            $table->timestamps();
            $table->primary('uuid');
        });

        // re-insert data from satis_configurations_tmp table
        foreach (DB::table('satis_configurations_tmp')->select()->get() as $record) {
            DB::table('satis_configurations')->insert([
                'uuid' => $record->uuid,
                'name' => $record->name,
                'homepage' => $record->homepage,
                'crontab' => $record->crontab,
                'configuration' => $record->configuration,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
        }

        // get rid of tmp table
        Schema::drop('satis_configurations_tmp');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        // rename current table in order to populate the data back to the old structure
        Schema::rename('satis_configurations', 'satis_configurations_tmp');

        // create old structure
        Schema::create('satis_configurations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('homepage')->unique();
            $table->string('uuid')->unique();
            $table->string('crontab')->nullable()->default('');
            $table->longText('configuration');
            $table->timestamps();
        });

        // re-insert data from satis_configurations_tmp table
        foreach (DB::table('satis_configurations_tmp')->select()->get() as $record) {
            DB::table('satis_configurations')->insert([
                'name' => $record->name,
                'homepage' => $record->homepage,
                'uuid' => $record->uuid,
                'crontab' => $record->crontab,
                'configuration' => $record->configuration,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ]);
        }

        // get rid of tmp table
        Schema::drop('satis_configurations_tmp');
    }
}
