<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('list_parameter', function (Blueprint $table) {
            $table->id();
            $table->string('nama_parameter', 100)->unique();
            $table->string('parameter_utama', 50)->nullable();
            $table->string('default_satuan', 20)->nullable();
            $table->string('default_kolom_sensor', 20)->nullable();
            $table->unsignedInteger('default_parameter_group_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active', 'idx_list_parameter_active');
            $table->index('default_parameter_group_id', 'idx_list_parameter_group');
            $table->foreign('default_parameter_group_id', 'fk_list_parameter_group')
                ->references('id')
                ->on('parameter_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        Schema::create('template_kategori_parameter', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('id_katlogger');
            $table->unsignedBigInteger('list_parameter_id');
            $table->unsignedInteger('urutan')->default(0);
            $table->string('kolom_sensor_default', 20)->nullable();
            $table->string('satuan_override', 20)->nullable();
            $table->unsignedInteger('parameter_group_id')->nullable();
            $table->timestamps();

            $table->unique(['id_katlogger', 'list_parameter_id'], 'uq_template_kategori_parameter');
            $table->index('urutan', 'idx_template_kategori_parameter_urutan');
            $table->index('parameter_group_id', 'idx_template_kategori_parameter_group');

            $table->foreign('id_katlogger', 'fk_template_kategori')
                ->references('id_katlogger')
                ->on('kategori_logger')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('list_parameter_id', 'fk_template_list_parameter')
                ->references('id')
                ->on('list_parameter')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('parameter_group_id', 'fk_template_parameter_group')
                ->references('id')
                ->on('parameter_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_kategori_parameter');
        Schema::dropIfExists('list_parameter');
    }
};
