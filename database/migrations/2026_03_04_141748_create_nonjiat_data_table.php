<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('nonjiat_data', function (Blueprint $table) {
            $table->charset   = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 25);
            $table->decimal('jarak_sensor_ke_air', 10, 4)->nullable()->comment('Jarak sensor ke permukaan air (m)');
            $table->decimal('tinggi_sensor', 10, 4)->nullable()->comment('Tinggi sensor dari dasar (m)');

            $table->index('id_logger', 'idx_nonjiat_logger');
            $table->foreign('id_logger', 'fk_nonjiat_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nonjiat_data');
    }
};
