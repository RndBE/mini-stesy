<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel konfigurasi untuk logger AFMR tipe Contact (sensor kontak langsung).
     */
    public function up(): void
    {
        Schema::create('afmr_contact_data', function (Blueprint $table) {
            $table->charset   = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('id_logger', 25);
            $table->decimal('lebar_sungai', 10, 4)->nullable()->comment('Lebar sungai pengukuran (m)');
            $table->decimal('kedalaman_rata', 10, 4)->nullable()->comment('Kedalaman rata-rata sungai (m)');
            $table->decimal('koefisien_debit', 10, 6)->nullable()->comment('Koefisien K dalam rumus Q = K * A * V');
            $table->text('catatan')->nullable()->comment('Catatan tambahan konfigurasi sensor contact');

            $table->index('id_logger', 'idx_afmr_contact_logger');
            $table->foreign('id_logger', 'fk_afmr_contact_logger')
                  ->references('id_logger')->on('t_logger')
                  ->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afmr_contact_data');
    }
};
