<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel konfigurasi untuk logger AFMR tipe Non-Contact (sensor radar/Doppler).
     */
    public function up(): void
    {
        Schema::create('afmr_noncontact_data', function (Blueprint $table) {
            $table->charset   = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 25);
            $table->decimal('tinggi_sensor', 10, 4)->nullable()->comment('Tinggi sensor dari dasar sungai (m)');
            $table->decimal('jarak_sensor_ke_air', 10, 4)->nullable()->comment('Jarak sensor ke permukaan air (m)');
            $table->decimal('elevasi_max', 10, 4)->nullable()->comment('Batas skala atas elevasi muka air (m)');
            $table->decimal('elevasi_min', 10, 4)->nullable()->comment('Batas skala bawah elevasi muka air (m)');
            $table->text('catatan')->nullable()->comment('Catatan tambahan konfigurasi sensor non-contact');

            $table->index('id_logger', 'idx_afmr_noncontact_logger');
            $table->foreign('id_logger', 'fk_afmr_noncontact_logger')
                  ->references('id_logger')->on('t_logger')
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afmr_noncontact_data');
    }
};
