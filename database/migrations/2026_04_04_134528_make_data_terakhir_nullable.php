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
            // Make old columns nullable if they exist
            if (Schema::hasColumn('t_perbaikan', 'data_terakhir')) {
                $table->text('data_terakhir')->nullable()->change();
            }
            if (Schema::hasColumn('t_perbaikan', 'tabel')) {
                $table->string('tabel', 15)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_perbaikan', function (Blueprint $table) {
            if (Schema::hasColumn('t_perbaikan', 'data_terakhir')) {
                $table->text('data_terakhir')->nullable(false)->change();
            }
            if (Schema::hasColumn('t_perbaikan', 'tabel')) {
                $table->string('tabel', 15)->nullable(false)->change();
            }
        });
    }
};
