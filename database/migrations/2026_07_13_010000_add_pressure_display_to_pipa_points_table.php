<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pipa_points')) {
            return;
        }

        if (! Schema::hasColumn('pipa_points', 'pressure_display')) {
            Schema::table('pipa_points', function (Blueprint $table) {
                $table->string('pressure_display', 20)->default('auto')->after('pressure');
            });
        }

        $this->applyDefaultPressureDisplay();
    }

    public function down(): void
    {
        if (! Schema::hasTable('pipa_points') || ! Schema::hasColumn('pipa_points', 'pressure_display')) {
            return;
        }

        Schema::table('pipa_points', function (Blueprint $table) {
            $table->dropColumn('pressure_display');
        });
    }

    private function applyDefaultPressureDisplay(): void
    {
        $pressure1 = ['10373', '10374', '10375', '10376', '10378', '10377', '10379'];
        $pressure2 = ['10370', '10368', '10369', '10371', '10372'];

        DB::table('pipa_points')->whereIn('logger_id', $pressure1)->update(['pressure_display' => 'pressure_1']);
        DB::table('pipa_points')->whereIn('logger_id', $pressure2)->update(['pressure_display' => 'pressure_2']);
    }
};
