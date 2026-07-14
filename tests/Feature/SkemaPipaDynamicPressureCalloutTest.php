<?php

namespace Tests\Feature;

use App\Http\Middleware\AuditLogMiddleware;
use App\Models\t_User;
use Database\Seeders\PipaPointSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkemaPipaDynamicPressureCalloutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware(AuditLogMiddleware::class);

        foreach ([
            'pipa_points',
            'temp_s50_latest',
            'temp_s19_latest',
            'temp_s16_latest',
            'parameter_sensor',
            't_logger',
            't_user',
            'instansi',
            'role_permissions',
            'permissions',
            'roles',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
        });

        // Tabel RBAC minimal supaya sidebar (memanggil hasPermission) bisa render
        // saat menguji halaman untuk user non-superadmin.
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role_name');
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('permission_name');
        });
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->unsignedInteger('role_id');
            $table->unsignedInteger('permission_id');
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->string('status')->default('aktif');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->unsignedTinyInteger('decimal_places')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger')->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
            $table->string('tabel_main');
            $table->integer('jeda_notif')->default(1);
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count')->default(16);
            $table->string('status_perbaikan')->nullable();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id');
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('satuan')->nullable();
            $table->string('tipe_graf')->nullable();
            $table->string('icon_app')->nullable();
            $table->string('debit_awlr')->nullable();
            $table->string('parameter_utama')->nullable();
            $table->unsignedInteger('parameter_group_id')->nullable();
        });

        $this->createLatestTable('temp_s16_latest', 16);
        $this->createLatestTable('temp_s19_latest', 19);
        $this->createLatestTable('temp_s50_latest', 50);

        Schema::create('pipa_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('scheme', 50);
            $table->string('logger_id', 15)->nullable();
            $table->string('label')->nullable();
            $table->string('kind', 20)->default('outlet');
            $table->decimal('x', 6, 3)->default(0);
            $table->decimal('y', 6, 3)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->decimal('pressure', 10, 2)->nullable();
            $table->string('pressure_display', 20)->default('auto');
            $table->decimal('flowrate', 10, 2)->nullable();
            $table->decimal('totalizer', 14, 2)->nullable();
            $table->timestamps();
        });
    }

    public function test_pipe_callout_payload_exposes_dynamic_pressure_values(): void
    {
        $user = t_User::create([
            'nama' => 'Super Admin',
            'username' => 'super',
            'password' => 'secret',
            'level_user' => 'superadmin',
        ]);

        DB::table('t_logger')->insert([
            'id_logger' => '10373',
            'nama_logger' => 'DMA 1 MOJOLABAN',
            'tabel_main' => 't_s16_01',
            'id_katlogger' => 3,
            'sensor_count' => 16,
        ]);

        DB::table('temp_s16_latest')->insert([
            'id_logger' => '10373',
            'waktu' => now(),
            'sensor1' => 12.34,
            'sensor2' => 4567,
            'sensor6' => 1.11,
            'sensor7' => 0,
            'is_online' => true,
        ]);

        $parameters = collect([
            [
                'logger_id' => '10373',
                'nama_parameter' => 'Flowrate',
                'kolom_sensor' => 'sensor1',
                'satuan' => 'liter/s',
            ],
            [
                'logger_id' => '10373',
                'nama_parameter' => 'Totalizer 1',
                'kolom_sensor' => 'sensor2',
                'satuan' => 'm3',
            ],
            [
                'logger_id' => '10373',
                'nama_parameter' => 'Pressure 1',
                'kolom_sensor' => 'sensor6',
                'satuan' => 'bar',
                'parameter_utama' => 'pressure_1',
            ],
            [
                'logger_id' => '10373',
                'nama_parameter' => 'Pressure 2',
                'kolom_sensor' => 'sensor7',
                'satuan' => 'bar',
                'parameter_utama' => 'pressure_2',
            ],
        ])->map(fn (array $row) => array_merge([
            'satuan' => null,
            'tipe_graf' => null,
            'icon_app' => null,
            'debit_awlr' => null,
            'parameter_utama' => null,
            'parameter_group_id' => null,
        ], $row))->all();

        DB::table('parameter_sensor')->insert($parameters);

        DB::table('pipa_points')->insert([
            [
                'scheme' => 'plesungan',
                'logger_id' => '10373',
                'label' => 'DMA 1 MOJOLABAN',
                'kind' => 'outlet',
                'x' => 25.5,
                'y' => 51.25,
                'sort_order' => 1,
                'pressure_display' => 'auto',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'scheme' => 'plesungan',
                'logger_id' => '10373',
                'label' => 'DMA 1 PRESSURE 2',
                'kind' => 'outlet',
                'x' => 35.5,
                'y' => 61.25,
                'sort_order' => 2,
                'pressure_display' => 'pressure_2',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $html = $this->actingAs($user)
            ->get(route('skema-pipa', 'plesungan'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('"pressure_1":1.11', $html);
        $this->assertStringContainsString('"pressure_2":0', $html);
        $this->assertStringContainsString('"pressure_display":"auto"', $html);
        $this->assertStringContainsString('"pressure_display":"pressure_2"', $html);
        $this->assertStringContainsString('id="pc-pressure-rows"', $html);
        $this->assertStringContainsString('id="pc-title"', $html);
        $this->assertStringContainsString('id="pc-status"', $html);
        $this->assertStringContainsString('id="pc-updated"', $html);
        $this->assertStringContainsString('class="pc-metric__value"', $html);
        $this->assertStringContainsString('aria-label="Tutup callout"', $html);
        $this->assertStringNotContainsString('>CL</button>', $html);
    }

    public function test_pipa_point_seeder_sets_pressure_display_for_known_dmas(): void
    {
        $this->seed(PipaPointSeeder::class);

        $expected = [
            '10373' => 'pressure_1',
            '10374' => 'pressure_1',
            '10375' => 'pressure_1',
            '10376' => 'pressure_1',
            '10378' => 'pressure_1',
            '10370' => 'pressure_2',
            '10377' => 'pressure_1',
            '10368' => 'pressure_2',
            '10369' => 'pressure_2',
            '10379' => 'pressure_1',
            '10371' => 'pressure_2',
            '10372' => 'pressure_2',
        ];

        foreach ($expected as $loggerId => $display) {
            $this->assertDatabaseHas('pipa_points', [
                'logger_id' => $loggerId,
                'pressure_display' => $display,
            ]);
        }
    }

    public function test_skema_pipa_is_forbidden_for_non_wosusokas_user(): void
    {
        $otherId = DB::table('instansi')->insertGetId(['nama' => 'PDAM Lain']);

        $user = t_User::create([
            'nama' => 'Operator Lain',
            'username' => 'lain',
            'password' => 'secret',
            'level_user' => 'pegawai',
            'instansi_id' => $otherId,
        ]);

        $this->actingAs($user)
            ->get(route('skema-pipa', 'plesungan'))
            ->assertForbidden();
    }

    public function test_skema_pipa_is_visible_for_wosusokas_user(): void
    {
        $wosusokasId = DB::table('instansi')->insertGetId(['nama' => 'SPAM WOSUSOKAS']);

        $user = t_User::create([
            'nama' => 'Operator Wosusokas',
            'username' => 'wosu',
            'password' => 'secret',
            'level_user' => 'pegawai',
            'instansi_id' => $wosusokasId,
        ]);

        $this->actingAs($user)
            ->get(route('skema-pipa', 'plesungan'))
            ->assertOk();
    }

    public function test_manage_endpoints_reject_non_admin_wosusokas_user(): void
    {
        $wosusokasId = DB::table('instansi')->insertGetId(['nama' => 'SPAM WOSUSOKAS']);

        $pegawai = t_User::create([
            'nama' => 'Pegawai Wosusokas',
            'username' => 'pegawai_wosu',
            'password' => 'secret',
            'level_user' => 'pegawai',
            'instansi_id' => $wosusokasId,
        ]);

        // Pegawai (bukan admin) tidak boleh menambah titik.
        $this->actingAs($pegawai)
            ->postJson(route('skema-pipa.points.store'), [
                'scheme' => 'plesungan',
                'kind' => 'outlet',
                'x' => 10,
                'y' => 20,
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('pipa_points', 0);
    }

    public function test_manage_endpoints_allow_wosusokas_admin(): void
    {
        $wosusokasId = DB::table('instansi')->insertGetId(['nama' => 'SPAM WOSUSOKAS']);

        $admin = t_User::create([
            'nama' => 'Admin Wosusokas',
            'username' => 'admin_wosu',
            'password' => 'secret',
            'level_user' => 'instansi_admin',
            'instansi_id' => $wosusokasId,
        ]);

        $this->actingAs($admin)
            ->postJson(route('skema-pipa.points.store'), [
                'scheme' => 'plesungan',
                'kind' => 'outlet',
                'x' => 10,
                'y' => 20,
            ])
            ->assertOk()
            ->assertJson(['ok' => true]);

        $this->assertDatabaseHas('pipa_points', ['scheme' => 'plesungan', 'kind' => 'outlet']);
    }

    private function createLatestTable(string $tableName, int $sensorCount): void
    {
        Schema::create($tableName, function (Blueprint $table) use ($sensorCount) {
            $table->increments('id');
            $table->string('id_logger');
            $table->dateTime('waktu')->nullable();
            for ($i = 1; $i <= $sensorCount; $i++) {
                $table->double('sensor' . $i)->nullable();
            }
            $table->boolean('is_online')->default(true);
        });
    }
}
