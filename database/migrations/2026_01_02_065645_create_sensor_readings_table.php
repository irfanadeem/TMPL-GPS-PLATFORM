<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSensorReadingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('device_id', 50);
            $table->string('sensor_type', 50);
            $table->decimal('sensor_value', 10, 2);
            $table->dateTime('timestamp');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['device_id', 'timestamp'], 'idx_device_time');
            $table->index('sensor_type', 'idx_sensor_type');
            $table->index('timestamp', 'idx_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sensor_readings');
    }
}
