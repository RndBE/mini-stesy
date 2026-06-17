<?php

namespace Tests\Feature;

use App\Http\Controllers\TingkatSiagaAwlrController;
use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TingkatSiagaUnifiedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['tingkat_siaga_awlr', 'klasifikasi_hujan', 't_logger', 't_lokasi', 'kategori_logger', 't_user'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('idlokasi');
            $table->string('nama_lokasi');
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger');
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
            $table->integer('jeda_notif')->nullable();
        });

        Schema::create('tingkat_siaga_awlr', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->integer('id_status');
            $table->string('nama');
            $table->decimal('nilai', 10, 2)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->string('warna')->nullable();
        });

        Schema::create('klasifikasi_hujan', function (Blueprint $table) {
            $table->increments('id_klasifikasi');
            $table->string('logger_id', 15);
            $table->string('waktu', 10);
            $table->string('debit_air', 255);
            $table->string('intensitas', 255);
            $table->tinyInteger('status')->default(1);
        });

        $this->actingAs(t_User::create([
            'nama' => 'Super Admin', 'username' => 'super', 'password' => 'secret', 'level_user' => 'superadmin',
        ]));

        DB::table('kategori_logger')->insert([
            ['id_katlogger' => 1, 'nama_kategori' => 'AWLR'],
            ['id_katlogger' => 2, 'nama_kategori' => 'ARR'],
        ]);
        DB::table('t_lokasi')->insert([
            ['idlokasi' => 1, 'nama_lokasi' => 'Pos AWLR'],
            ['idlokasi' => 2, 'nama_lokasi' => 'Pos ARR'],
        ]);
        DB::table('t_logger')->insert([
            ['id_logger' => '10366', 'nama_logger' => 'AWLR Seturan', 'tabel_main' => 't_s16_01', 'idlokasi' => 1, 'id_katlogger' => 1, 'sensor_count' => 16, 'jeda_notif' => 1],
            ['id_logger' => '10002', 'nama_logger' => 'ARR Pogung',   'tabel_main' => 't_s16_01', 'idlokasi' => 2, 'id_katlogger' => 2, 'sensor_count' => 16, 'jeda_notif' => 1],
        ]);
    }

    private function rows(): array
    {
        $request = Request::create('/tingkat-siaga-awlr', 'GET');
        return collect(app(TingkatSiagaAwlrController::class)->index($request)->getData()['rows'])
            ->keyBy('id_logger')->all();
    }

    public function test_index_lists_both_awlr_and_arr_with_tipe(): void
    {
        $rows = $this->rows();

        $this->assertArrayHasKey('10366', $rows);
        $this->assertArrayHasKey('10002', $rows);
        $this->assertSame('AWLR', $rows['10366']['tipe']);
        $this->assertSame('ARR', $rows['10002']['tipe']);
    }

    public function test_arr_row_exposes_six_perjam_and_perhari_thresholds(): void
    {
        $arr = $this->rows()['10002'];

        $this->assertCount(6, $arr['klasifikasi']['perjam']);
        $this->assertCount(6, $arr['klasifikasi']['perhari']);
        $this->assertSame('Tidak Hujan', $arr['klasifikasi']['perjam'][0]['intensitas']);
        $this->assertSame('Hujan Sangat Lebat', $arr['klasifikasi']['perjam'][5]['intensitas']);
    }

    public function test_update_arr_saves_thresholds_toggle_and_jeda(): void
    {
        $payload = [
            'status_notifikasi' => 1,
            'jeda_notif' => 15,
            'klasifikasi' => [
                'perjam'  => [['debit_air' => 0], ['debit_air' => 0.1], ['debit_air' => 1], ['debit_air' => 5], ['debit_air' => 10], ['debit_air' => 20]],
                'perhari' => [['debit_air' => 0], ['debit_air' => 0.1], ['debit_air' => 5], ['debit_air' => 20], ['debit_air' => 50], ['debit_air' => 100]],
            ],
        ];

        $request = Request::create('/tingkat-siaga-awlr/10002', 'PUT', $payload);
        $response = app(TingkatSiagaAwlrController::class)->update($request, '10002')->getData(true);

        $this->assertTrue($response['success']);
        $this->assertSame(12, DB::table('klasifikasi_hujan')->where('logger_id', '10002')->count());
        $this->assertSame(12, DB::table('klasifikasi_hujan')->where('logger_id', '10002')->where('status', 1)->count());
        $this->assertSame(15, (int) DB::table('t_logger')->where('id_logger', '10002')->value('jeda_notif'));

        $sedang = DB::table('klasifikasi_hujan')
            ->where('logger_id', '10002')->where('waktu', 'perjam')->where('intensitas', 'Hujan Sedang')->first();
        $this->assertEquals(5.0, (float) $sedang->debit_air);
    }

    public function test_update_arr_toggle_off_sets_status_zero(): void
    {
        DB::table('klasifikasi_hujan')->insert([
            ['logger_id' => '10002', 'waktu' => 'perjam', 'debit_air' => '5', 'intensitas' => 'Hujan Sedang', 'status' => 1],
        ]);

        $payload = ['status_notifikasi' => 0];
        $request = Request::create('/tingkat-siaga-awlr/10002', 'PUT', $payload);
        app(TingkatSiagaAwlrController::class)->update($request, '10002');

        $this->assertSame(0, DB::table('klasifikasi_hujan')->where('logger_id', '10002')->where('status', 1)->count());
    }

    public function test_update_awlr_still_writes_siaga_levels(): void
    {
        $payload = [
            'status_notifikasi' => 1,
            'jeda_notif' => 10,
            'levels' => [
                ['nama' => 'Waspada', 'nilai' => 1.5, 'warna' => '#FACC15'],
                ['nama' => 'Siaga',   'nilai' => 2.5, 'warna' => '#F97316'],
            ],
        ];

        $request = Request::create('/tingkat-siaga-awlr/10366', 'PUT', $payload);
        $response = app(TingkatSiagaAwlrController::class)->update($request, '10366')->getData(true);

        $this->assertTrue($response['success']);
        $this->assertSame(2, DB::table('tingkat_siaga_awlr')->where('id_logger', '10366')->where('status', 1)->count());
        $this->assertEqualsCanonicalizing(
            ['Waspada', 'Siaga'],
            DB::table('tingkat_siaga_awlr')->where('id_logger', '10366')->pluck('nama')->all()
        );
    }
}
