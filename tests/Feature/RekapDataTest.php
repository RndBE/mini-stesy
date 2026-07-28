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

        foreach (['t_s16_01', 't_lokasi', 't_logger', 't_user'] as $table) {
            Schema::dropIfExists($table);
        }

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
        $this->assertStringContainsString('x-model="loggerFilterDraft"', $source);
        $this->assertStringContainsString('x-teleport="body"', $source);
        $this->assertStringContainsString('displayedLoggers', $source);
        $this->assertStringContainsString('Pilih Lokasi', $source);
        $this->assertStringContainsString('$logger->nama_pos', $source);
        $this->assertStringNotContainsString('Pilih Logger', $source);
        $this->assertStringNotContainsString('Nama Logger', $source);
    }

    private function insertLogger(string $id, string $name, ?int $locationId = null): void
    {
        DB::table('t_logger')->insert([
            'id_logger' => $id,
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
