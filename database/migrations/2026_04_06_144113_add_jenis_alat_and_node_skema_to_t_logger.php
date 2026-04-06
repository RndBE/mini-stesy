<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom identifikasi tipe alat (AWLR/AWGC/OTHER)
     * dan node_skema_id sebagai penghubung ke node visual pada Skema Irigasi.
     */
    public function up(): void
    {
        Schema::table('t_logger', function (Blueprint $table) {
            // Tipe alat: AWLR = sensor pemantau, AWGC = kontrol pintu air, OTHER = lainnya
            $table->enum('jenis_alat', ['AWLR', 'AWGC', 'OTHER'])
                  ->default('OTHER')
                  ->nullable()
                  ->after('id_katlogger')
                  ->comment('Jenis alat: AWLR=Sensor TMA, AWGC=Kontrol Pintu Air');

            // ID node skema irigasi (misal: BGP_1, WEIR_COPONG, BLG_1)
            // Menjadi penghubung antara device fisik dan visual topology
            $table->string('node_skema_id', 50)
                  ->nullable()
                  ->after('jenis_alat')
                  ->comment('ID node pada Skema Irigasi SVG (mis: WEIR_COPONG, BGP_1)');

            // Parameter tambahan khusus AWGC: batas fisik bukaan pintu
            $table->unsignedSmallInteger('bukaan_maksimal_cm')
                  ->nullable()
                  ->after('node_skema_id')
                  ->comment('Batas maksimal bukaan pintu AWGC dalam cm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_logger', function (Blueprint $table) {
            $table->dropColumn(['jenis_alat', 'node_skema_id', 'bukaan_maksimal_cm']);
        });
    }
};
