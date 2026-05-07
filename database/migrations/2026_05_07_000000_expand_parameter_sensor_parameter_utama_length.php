<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->resizeParameterUtama(50);
    }

    public function down(): void
    {
        $this->resizeParameterUtama(20);
    }

    private function resizeParameterUtama(int $length): void
    {
        if (!Schema::hasTable('parameter_sensor') || !Schema::hasColumn('parameter_sensor', 'parameter_utama')) {
            return;
        }

        match (DB::getDriverName()) {
            'mysql', 'mariadb' => DB::statement("ALTER TABLE parameter_sensor MODIFY parameter_utama VARCHAR({$length}) NULL"),
            'pgsql' => DB::statement("ALTER TABLE parameter_sensor ALTER COLUMN parameter_utama TYPE VARCHAR({$length})"),
            default => null,
        };
    }
};
