<?php

namespace Tests\Feature;

use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RekapDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-06-23 10:30:00', config('app.timezone')));

        foreach (['t_s16_01', 't_lokasi', 't_logger', 't_user', 'instansi'] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama')->unique();
            $table->timestamps();
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('idlokasi');
            $table->string('nama_lokasi')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger')->nullable();
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
        });

        Schema::create('t_s16_01', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            $table->double('sensor1')->nullable();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_rekap_api_uses_location_name_and_keeps_logger_id(): void
    {
        $locationId = DB::table('t_lokasi')->insertGetId([
            'nama_lokasi' => 'Bendung Mojolaban',
        ]);

        $this->insertLogger('20092', 'Nama Logger Internal', $locationId);

        $response = $this->actingAs($this->superadmin())
            ->getJson('/api/rekap-data?start_date=2026-06-20&end_date=2026-06-20');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('loggers.0.id', '20092')
            ->assertJsonPath('loggers.0.name', 'Bendung Mojolaban');
    }

    public function test_missing_minutes_api_uses_location_name(): void
    {
        $locationId = DB::table('t_lokasi')->insertGetId([
            'nama_lokasi' => 'Pos Intake Utara',
        ]);

        $this->insertLogger('L-01', 'Logger Intake', $locationId);

        $response = $this->actingAs($this->superadmin())
            ->getJson('/api/rekap-data/missing-minutes?logger_id=L-01&date=2026-06-20');

        $response->assertOk()
            ->assertJsonPath('logger_id', 'L-01')
            ->assertJsonPath('logger_name', 'Pos Intake Utara');
    }

    public function test_rekap_api_falls_back_to_logger_name_without_location(): void
    {
        $this->insertLogger('L-02', 'Logger Tanpa Lokasi');

        $response = $this->actingAs($this->superadmin())
            ->getJson('/api/rekap-data?start_date=2026-06-20&end_date=2026-06-20');

        $response->assertOk()
            ->assertJsonPath('loggers.0.name', 'Logger Tanpa Lokasi');
    }

    public function test_rekap_view_defines_location_filter_modal_and_location_labels(): void
    {
        $source = file_get_contents(resource_path('views/rekap-data/index.blade.php'));

        $this->assertStringContainsString('Filter Lokasi', $source);
        $this->assertStringContainsString('Pilih Lokasi yang Ditampilkan', $source);
        $this->assertStringContainsString('loggerFilterDraft', $source);
        $this->assertStringContainsString('x-teleport="body"', $source);
        $this->assertStringContainsString('displayedLoggers', $source);
        $this->assertStringContainsString('Pilih Lokasi', $source);
        $this->assertStringContainsString('$logger->nama_pos', $source);
        $this->assertStringContainsString('getLoggerGroups()', $source);
        $this->assertStringContainsString('isProjectGroupAllChecked(group)', $source);
        $this->assertStringContainsString('isProjectGroupIndeterminate(group)', $source);
        $this->assertStringContainsString('$el.indeterminate', $source);
        $this->assertStringContainsString('toggleProjectGroup(group, $event.target.checked)', $source);
        $this->assertStringContainsString('toggleLoggerOption(logger.id, $event.target.checked)', $source);
        $this->assertStringNotContainsString('Pilih Logger', $source);
        $this->assertStringNotContainsString('Nama Logger', $source);
    }

    public function test_rekap_view_groups_distinct_logger_ids_by_project_without_merging_location(): void
    {
        $projectId = DB::table('instansi')->insertGetId([
            'nama' => 'Project Bengawan Solo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $locationId = DB::table('t_lokasi')->insertGetId([
            'nama_lokasi' => 'Bendung Mojolaban',
        ]);

        $this->insertLogger('L-101', 'Logger Satu', $locationId, $projectId);
        $this->insertLogger('L-102', 'Logger Dua', $locationId, $projectId);

        $html = $this->actingAs($this->superadmin())
            ->get('/rekap-data')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Project Bengawan Solo', $html);
        $this->assertStringContainsString('Bendung Mojolaban', $html);
        $this->assertStringContainsString('L-101', $html);
        $this->assertStringContainsString('L-102', $html);
        $this->assertStringContainsString("'instansi_id' =>", file_get_contents(
            resource_path('views/rekap-data/index.blade.php')
        ));
    }

    private function insertLogger(string $id, string $name, ?int $locationId = null, ?int $projectId = null): void
    {
        DB::table('t_logger')->insert([
            'id_logger' => $id,
            'instansi_id' => $projectId,
            'nama_logger' => $name,
            'tabel_main' => 't_s16_01',
            'idlokasi' => $locationId,
            'sensor_count' => 16,
        ]);
    }

    private function superadmin(): t_User
    {
        return t_User::create([
            'nama' => 'Super Admin',
            'username' => 'superadmin',
            'password' => 'secret',
            'level_user' => 'superadmin',
            'status' => 'aktif',
        ]);
    }
}
