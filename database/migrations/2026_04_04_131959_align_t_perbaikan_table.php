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
        Schema::table('t_perbaikan', function (Blueprint $table) {
            if (!Schema::hasColumn('t_perbaikan', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('id_logger');
            }
            if (!Schema::hasColumn('t_perbaikan', 'tanggal_perbaikan')) {
                $table->date('tanggal_perbaikan')->nullable()->after('keterangan');
            }
            if (!Schema::hasColumn('t_perbaikan', 'petugas')) {
                $table->string('petugas', 255)->nullable()->after('tanggal_perbaikan');
            }
            if (!Schema::hasColumn('t_perbaikan', 'status_akhir')) {
                $table->string('status_akhir', 30)->default('sedang_perbaikan')->after('petugas');
            }
            if (!Schema::hasColumn('t_perbaikan', 'created_by')) {
                $table->string('created_by', 255)->nullable()->after('status_akhir');
            }
            if (!Schema::hasColumn('t_perbaikan', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_perbaikan', function (Blueprint $table) {
            $table->dropColumn([
                'keterangan',
                'tanggal_perbaikan',
                'petugas',
                'status_akhir',
                'created_by',
                'created_at',
                'updated_at'
            ]);
        });
    }
};
