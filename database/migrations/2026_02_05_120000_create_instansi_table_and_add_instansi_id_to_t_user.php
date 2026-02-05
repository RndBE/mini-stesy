<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instansi', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments('id');
            $table->string('nama', 100)->unique();
            $table->text('alamat')->nullable();
            $table->string('telp', 25)->nullable();
            $table->timestamps();
        });

        Schema::table('t_user', function (Blueprint $table) {
            $table->unsignedInteger('instansi_id')->nullable()->after('instansi');
            $table->index('instansi_id', 'idx_user_instansi');
            $table->foreign('instansi_id', 'fk_user_instansi')
                ->references('id')
                ->on('instansi')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });

        if (Schema::hasColumn('t_user', 'instansi')) {
            $names = DB::table('t_user')
                ->whereNotNull('instansi')
                ->distinct()
                ->pluck('instansi');

            foreach ($names as $name) {
                $instansiId = DB::table('instansi')->insertGetId([
                    'nama' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('t_user')
                    ->where('instansi', $name)
                    ->update(['instansi_id' => $instansiId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('t_user', function (Blueprint $table) {
            $table->dropForeign('fk_user_instansi');
            $table->dropIndex('idx_user_instansi');
            $table->dropColumn('instansi_id');
        });

        Schema::dropIfExists('instansi');
    }
};
