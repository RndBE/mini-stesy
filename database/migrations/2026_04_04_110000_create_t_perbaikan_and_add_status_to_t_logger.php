<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom status_perbaikan ke t_logger (jika belum ada)
        if (!Schema::hasColumn('t_logger', 'status_perbaikan')) {
            Schema::table('t_logger', function (Blueprint $table) {
                $table->string('status_perbaikan', 20)->default('normal')->after('sensor_count');
            });
        }

        // 2. Buat tabel t_perbaikan jika belum ada
        if (!Schema::hasTable('t_perbaikan')) {
            Schema::create('t_perbaikan', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('id_logger', 15);
                $table->text('keterangan');
                $table->date('tanggal_perbaikan');
                $table->string('petugas', 255);
                $table->string('status_akhir', 30)->default('sedang_perbaikan');
                $table->string('created_by', 255)->nullable();
                $table->timestamps();

                $table->foreign('id_logger')
                    ->references('id_logger')
                    ->on('t_logger')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();

                $table->index('id_logger');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('t_perbaikan');

        if (Schema::hasColumn('t_logger', 'status_perbaikan')) {
            Schema::table('t_logger', function (Blueprint $table) {
                $table->dropColumn('status_perbaikan');
            });
        }
    }
};
