<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->string('status', 20)->default('aktif')->after('level_user');
            $table->text('suspend_reason')->nullable()->after('status');
        });

        // Set semua user yang ada ke status aktif
        DB::table('t_user')->update(['status' => 'aktif']);
    }

    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->dropColumn(['status', 'suspend_reason']);
        });
    }
};
