<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('instansi') || !Schema::hasTable('t_user')) {
            return;
        }

        Schema::table('instansi', function (Blueprint $table) {
            if (!Schema::hasColumn('instansi', 'latitude')) {
                $table->string('latitude', 50)->nullable()->after('telp');
            }
            if (!Schema::hasColumn('instansi', 'longitude')) {
                $table->string('longitude', 50)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('instansi', 'zoom')) {
                $table->integer('zoom')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('instansi', 'logo')) {
                $table->string('logo', 255)->nullable()->after('zoom');
            }
            if (!Schema::hasColumn('instansi', 'logo_mobile')) {
                $table->string('logo_mobile', 255)->nullable()->after('logo');
            }
        });

        $availableCols = ['instansi_id'];
        foreach (['instansi', 'alamat', 'telp', 'latitude', 'longitude', 'zoom', 'logo', 'logo_mobile'] as $col) {
            if (Schema::hasColumn('t_user', $col)) {
                $availableCols[] = $col;
            }
        }

        $users = DB::table('t_user')
            ->whereNotNull('instansi_id')
            ->orderBy('id_user')
            ->get($availableCols)
            ->groupBy('instansi_id');

        foreach ($users as $instansiId => $rows) {
            $pick = fn(string $key) => optional($rows->first(function ($row) use ($key) {
                return isset($row->{$key}) && trim((string) $row->{$key}) !== '';
            }))->{$key};

            $payload = array_filter([
                'nama' => $pick('instansi'),
                'alamat' => $pick('alamat'),
                'telp' => $pick('telp'),
                'latitude' => $pick('latitude'),
                'longitude' => $pick('longitude'),
                'zoom' => $pick('zoom'),
                'logo' => $pick('logo'),
                'logo_mobile' => $pick('logo_mobile'),
            ], fn($v) => $v !== null && $v !== '');

            if (!empty($payload)) {
                DB::table('instansi')->where('id', (int) $instansiId)->update($payload);
            }
        }

        $dropColumns = array_values(array_filter([
            Schema::hasColumn('t_user', 'instansi') ? 'instansi' : null,
            Schema::hasColumn('t_user', 'alamat') ? 'alamat' : null,
            Schema::hasColumn('t_user', 'telp') ? 'telp' : null,
            Schema::hasColumn('t_user', 'latitude') ? 'latitude' : null,
            Schema::hasColumn('t_user', 'longitude') ? 'longitude' : null,
            Schema::hasColumn('t_user', 'zoom') ? 'zoom' : null,
            Schema::hasColumn('t_user', 'logo') ? 'logo' : null,
            Schema::hasColumn('t_user', 'logo_mobile') ? 'logo_mobile' : null,
        ]));

        if (!empty($dropColumns)) {
            Schema::table('t_user', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('instansi') || !Schema::hasTable('t_user')) {
            return;
        }

        Schema::table('t_user', function (Blueprint $table) {
            if (!Schema::hasColumn('t_user', 'instansi')) {
                $table->string('instansi', 50)->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('t_user', 'alamat')) {
                $table->text('alamat')->nullable()->after('instansi');
            }
            if (!Schema::hasColumn('t_user', 'telp')) {
                $table->string('telp', 25)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('t_user', 'latitude')) {
                $table->string('latitude', 50)->nullable()->after('telp');
            }
            if (!Schema::hasColumn('t_user', 'longitude')) {
                $table->string('longitude', 50)->nullable()->after('latitude');
            }
            if (!Schema::hasColumn('t_user', 'zoom')) {
                $table->integer('zoom')->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('t_user', 'logo')) {
                $table->string('logo', 255)->nullable()->after('zoom');
            }
            if (!Schema::hasColumn('t_user', 'logo_mobile')) {
                $table->string('logo_mobile', 255)->nullable()->after('logo');
            }
        });

        $instansiRows = DB::table('instansi')->get([
            'id',
            'nama',
            'alamat',
            'telp',
            'latitude',
            'longitude',
            'zoom',
            'logo',
            'logo_mobile',
        ]);

        foreach ($instansiRows as $instansi) {
            DB::table('t_user')
                ->where('instansi_id', $instansi->id)
                ->update([
                    'instansi' => $instansi->nama,
                    'alamat' => $instansi->alamat,
                    'telp' => $instansi->telp,
                    'latitude' => $instansi->latitude,
                    'longitude' => $instansi->longitude,
                    'zoom' => $instansi->zoom,
                    'logo' => $instansi->logo,
                    'logo_mobile' => $instansi->logo_mobile,
                ]);
        }

        $dropInstansiColumns = array_values(array_filter([
            Schema::hasColumn('instansi', 'latitude') ? 'latitude' : null,
            Schema::hasColumn('instansi', 'longitude') ? 'longitude' : null,
            Schema::hasColumn('instansi', 'zoom') ? 'zoom' : null,
            Schema::hasColumn('instansi', 'logo') ? 'logo' : null,
            Schema::hasColumn('instansi', 'logo_mobile') ? 'logo_mobile' : null,
        ]));

        if (!empty($dropInstansiColumns)) {
            Schema::table('instansi', function (Blueprint $table) use ($dropInstansiColumns) {
                $table->dropColumn($dropInstansiColumns);
            });
        }
    }
};
