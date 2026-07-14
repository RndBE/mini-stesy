<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pipa_points')) {
            return;
        }

        Schema::create('pipa_points', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->bigIncrements('id');
            $table->string('scheme', 50);                 // 'plesungan' | 'mojolaban'
            $table->string('logger_id', 15)->nullable();  // FK ke t_logger.id_logger (opsional)
            $table->string('label')->nullable();          // teks label pin (opsional; bisa ikut logger)
            $table->string('kind', 20)->default('outlet');// inlet | outlet | reservoir
            $table->decimal('x', 6, 3)->default(0);       // posisi persen (0-100)
            $table->decimal('y', 6, 3)->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            // Nilai statis untuk titik TANPA logger (mis. reservoir). Diabaikan bila
            // logger_id terisi (data diambil dari sensor).
            $table->decimal('pressure', 10, 2)->nullable();
            $table->string('pressure_display', 20)->default('auto'); // auto | pressure_1 | pressure_2 | both
            $table->decimal('flowrate', 10, 2)->nullable();
            $table->decimal('totalizer', 14, 2)->nullable();

            $table->timestamps();

            $table->index('scheme', 'idx_pipa_points_scheme');
            $table->index('logger_id', 'idx_pipa_points_logger');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipa_points');
    }
};
