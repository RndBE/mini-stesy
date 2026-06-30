<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [];
        foreach (['controller', 'tabel', 'temp_data'] as $column) {
            if (Schema::hasColumn('kategori_logger', $column)) {
                $columns[] = $column;
            }
        }

        if (!empty($columns)) {
            Schema::table('kategori_logger', function (Blueprint $table) use ($columns) {
                // Drop unique index on 'tabel' before dropping the column (required by SQLite).
                if (in_array('tabel', $columns)) {
                    $indexes = collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('kategori_logger')"))->pluck('name');
                    if ($indexes->contains('uq_kategori_tabel')) {
                        $table->dropUnique('uq_kategori_tabel');
                    }
                }
                $table->dropColumn($columns);
            });
        }
    }

    public function down(): void
    {
        Schema::table('kategori_logger', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_logger', 'controller')) {
                $table->string('controller', 20)->after('nama_kategori');
            }
            if (!Schema::hasColumn('kategori_logger', 'tabel')) {
                $table->string('tabel', 20)->after('controller');
                $table->unique('tabel', 'uq_kategori_tabel');
            }
            if (!Schema::hasColumn('kategori_logger', 'temp_data')) {
                $table->string('temp_data', 20)->after('kepanjangan');
            }
        });
    }
};
