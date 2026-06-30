<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parameter_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('kode_group', 30)->unique();
            $table->string('nama_group', 100);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });

        DB::table('parameter_groups')->insert([
            [
                'id' => 1,
                'kode_group' => 'LOGGER',
                'nama_group' => 'Kesehatan Logger',
                'deskripsi' => 'Parameter kesehatan/performa logger seperti baterai, suhu, dan kelembaban.',
                'sort_order' => 1,
            ],
            [
                'id' => 2,
                'kode_group' => 'ANGIN',
                'nama_group' => 'Parameter Angin',
                'deskripsi' => 'Parameter terkait angin seperti arah dan kecepatan.',
                'sort_order' => 2,
            ],
            [
                'id' => 3,
                'kode_group' => 'SUMUR',
                'nama_group' => 'Parameter Sumur',
                'deskripsi' => 'Parameter pengukuran utama sumur/air tanah.',
                'sort_order' => 3,
            ],
        ]);

        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->unsignedInteger('parameter_group_id')->nullable()->after('parameter_utama');
            $table->index('parameter_group_id', 'idx_param_group_id');
        });

        $loggerGroupId = (int) (DB::table('parameter_groups')->where('kode_group', 'LOGGER')->value('id') ?? 0);
        $anginGroupId = (int) (DB::table('parameter_groups')->where('kode_group', 'ANGIN')->value('id') ?? 0);
        $sumurGroupId = (int) (DB::table('parameter_groups')->where('kode_group', 'SUMUR')->value('id') ?? 0);

        if ($sumurGroupId) {
            DB::table('parameter_sensor')->update(['parameter_group_id' => $sumurGroupId]);
        }

        if ($loggerGroupId) {
            DB::table('parameter_sensor')
                ->whereRaw(
                    'LOWER(COALESCE(nama_parameter, "")) LIKE ? OR LOWER(COALESCE(parameter_utama, "")) LIKE ?',
                    ['%battery%', '%battery%']
                )
                ->orWhereRaw(
                    'LOWER(COALESCE(nama_parameter, "")) LIKE ? OR LOWER(COALESCE(parameter_utama, "")) LIKE ?',
                    ['%humidity%', '%humidity%']
                )
                ->orWhereRaw(
                    'LOWER(COALESCE(nama_parameter, "")) LIKE ? OR LOWER(COALESCE(parameter_utama, "")) LIKE ?',
                    ['%temperature%', '%temperature%']
                )
                ->update(['parameter_group_id' => $loggerGroupId]);
        }

        if ($anginGroupId) {
            DB::table('parameter_sensor')
                ->whereRaw(
                    'LOWER(COALESCE(nama_parameter, "")) LIKE ? OR LOWER(COALESCE(parameter_utama, "")) LIKE ?',
                    ['%angin%', '%angin%']
                )
                ->orWhereRaw(
                    'LOWER(COALESCE(nama_parameter, "")) LIKE ? OR LOWER(COALESCE(parameter_utama, "")) LIKE ?',
                    ['%wind%', '%wind%']
                )
                ->update(['parameter_group_id' => $anginGroupId]);
        }

        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->foreign('parameter_group_id', 'fk_param_group')
                ->references('id')
                ->on('parameter_groups')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        if (Schema::hasColumn('parameter_sensor', 'parameter_group')) {
            Schema::table('parameter_sensor', function (Blueprint $table) {
                // Drop the index before the column so SQLite doesn't object.
                if (collect(\Illuminate\Support\Facades\DB::select("PRAGMA index_list('parameter_sensor')"))->pluck('name')->contains('idx_param_group')) {
                    $table->dropIndex('idx_param_group');
                }
                $table->dropColumn('parameter_group');
            });
        }
    }

    public function down(): void
    {
        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->string('parameter_group', 20)->default('pengukuran')->after('parameter_utama');
        });

        DB::table('parameter_sensor')
            ->whereRaw('LOWER(COALESCE(nama_parameter, "")) LIKE ?', ['%battery%'])
            ->orWhereRaw('LOWER(COALESCE(parameter_utama, "")) LIKE ?', ['%battery%'])
            ->update(['parameter_group' => 'baterai']);

        Schema::table('parameter_sensor', function (Blueprint $table) {
            $table->dropForeign('fk_param_group');
            $table->dropIndex('idx_param_group_id');
            $table->dropColumn('parameter_group_id');
        });

        Schema::dropIfExists('parameter_groups');
    }
};

