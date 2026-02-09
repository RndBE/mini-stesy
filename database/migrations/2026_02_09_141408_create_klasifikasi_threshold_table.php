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
        Schema::create('klasifikasi_threshold', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->unsignedInteger('id_kategori');
            $table->string('state_key', 50); // 'tidak_hujan', 'hujan_ringan', etc.
            $table->string('state_label', 100); // 'Tidak Hujan', 'Hujan Ringan'
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->string('icon_path', 255); // '/icons/arr/tidak_hujan.svg'
            $table->string('color_hex', 7); // '#22c55e' for green
            $table->integer('sort_order')->default(0);

            $table->index('id_kategori', 'idx_threshold_kategori');
            $table->foreign('id_kategori', 'fk_threshold_kategori')
                ->references('id_katlogger')
                ->on('kategori_logger')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasi_threshold');
    }
};
