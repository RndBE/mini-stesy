<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('instansi') || Schema::hasColumn('instansi', 'judul_mobile')) {
            return;
        }

        Schema::table('instansi', function (Blueprint $table) {
            $table->string('judul_mobile', 120)->nullable()->after('nama');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('instansi') || !Schema::hasColumn('instansi', 'judul_mobile')) {
            return;
        }

        Schema::table('instansi', function (Blueprint $table) {
            $table->dropColumn('judul_mobile');
        });
    }
};
