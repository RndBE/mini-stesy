<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel audit trail perintah pintu air AWGC.
     * Setiap kali user memerintahkan perubahan posisi pintu, tercatat di sini.
     */
    public function up(): void
    {
        Schema::create('awgc_command_log', function (Blueprint $table) {
            $table->id();

            // Referensi ke node skema visual (misal: 'WEIR_COPONG', 'BGP_1')
            $table->string('node_skema_id', 50)->nullable()->index()
                  ->comment('ID node skema irigasi yang diperintah');

            // Referensi ke device fisik di lapangan
            $table->string('id_logger', 50)->nullable()->index()
                  ->comment('ID logger AWGC di lapangan');

            // Nilai perintah yang dikirim
            $table->unsignedSmallInteger('target_bukaan_cm')->nullable()
                  ->comment('Target posisi bukaan pintu dalam cm');
            $table->tinyInteger('target_bukaan_persen')->nullable()
                  ->comment('Target posisi bukaan dalam persen (0-100)');

            // Status siklus perintah
            $table->enum('status_command', ['pending', 'sent', 'success', 'error', 'timeout'])
                  ->default('pending')
                  ->comment('Status eksekusi perintah');
            $table->text('pesan_error')->nullable()
                  ->comment('Pesan error jika status=error');

            // Waktu-waktu kritis
            $table->timestamp('sent_at')->nullable()
                  ->comment('Waktu perintah dikirim ke MQTT');
            $table->timestamp('confirmed_at')->nullable()
                  ->comment('Waktu alat mengonfirmasi sukses');

            // Siapa yang memberikan perintah
            $table->unsignedBigInteger('commanded_by')->nullable()
                  ->comment('ID user yang memberikan perintah');
            $table->string('commanded_by_name', 100)->nullable()
                  ->comment('Nama user (snapshot, tidak FK)');

            $table->timestamps(); // created_at = waktu perintah dibuat

            // Index untuk monitoring dan audit
            $table->index(['node_skema_id', 'status_command']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('awgc_command_log');
    }
};
