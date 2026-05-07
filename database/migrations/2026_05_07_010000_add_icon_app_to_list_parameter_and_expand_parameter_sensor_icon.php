<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('list_parameter') && !Schema::hasColumn('list_parameter', 'icon_app')) {
            Schema::table('list_parameter', function (Blueprint $table) {
                $table->string('icon_app', 255)->nullable()->after('default_kolom_sensor');
            });
        }

        $this->resizeParameterSensorIcon(255);
    }

    public function down(): void
    {
        if (Schema::hasTable('parameter_sensor') && Schema::hasColumn('parameter_sensor', 'icon_app')) {
            DB::table('parameter_sensor')
                ->whereRaw('CHAR_LENGTH(COALESCE(icon_app, "")) > 20')
                ->update(['icon_app' => null]);
        }

        $this->resizeParameterSensorIcon(20);

        if (Schema::hasTable('list_parameter') && Schema::hasColumn('list_parameter', 'icon_app')) {
            Schema::table('list_parameter', function (Blueprint $table) {
                $table->dropColumn('icon_app');
            });
        }
    }

    private function resizeParameterSensorIcon(int $length): void
    {
        if (!Schema::hasTable('parameter_sensor') || !Schema::hasColumn('parameter_sensor', 'icon_app')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement("ALTER TABLE parameter_sensor MODIFY icon_app VARCHAR({$length}) NULL"),
            'pgsql' => DB::statement("ALTER TABLE parameter_sensor ALTER COLUMN icon_app TYPE VARCHAR({$length})"),
            default => null,
        };
    }
};
