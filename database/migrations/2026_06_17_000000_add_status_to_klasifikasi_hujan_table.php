<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-logger ARR notification on/off flag, mirroring tingkat_siaga_awlr.status.
     * Default 1 so existing rain classification rows stay active. Display on
     * Beranda is unaffected — only the notification path reads this column.
     */
    public function up(): void
    {
        if (!Schema::hasTable('klasifikasi_hujan')) {
            return;
        }

        if (!Schema::hasColumn('klasifikasi_hujan', 'status')) {
            Schema::table('klasifikasi_hujan', function (Blueprint $table) {
                $table->tinyInteger('status')->default(1)->after('intensitas');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('klasifikasi_hujan') && Schema::hasColumn('klasifikasi_hujan', 'status')) {
            Schema::table('klasifikasi_hujan', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
