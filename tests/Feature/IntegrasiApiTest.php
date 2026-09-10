<?php

namespace Tests\Feature;

use App\Models\t_User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IntegrasiApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-10 10:00:00', config('app.timezone')));

        foreach ([
            'temp_s16_latest', 'temp_s19_latest', 'temp_s50_latest',
            't_s16_01', 'parameter_sensor', 'kategori_logger',
            'user_logger_access', 't_logger', 't_lokasi', 't_user', 'instansi',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('instansi', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nama');
        });

        Schema::create('t_user', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('nama');
            $table->string('username');
            $table->string('password');
            $table->string('level_user');
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('status')->nullable();
            $table->string('suspend_reason')->nullable();
        });

        Schema::create('t_lokasi', function (Blueprint $table) {
            $table->increments('idlokasi');
            $table->string('nama_lokasi')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
        });

        Schema::create('t_logger', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15)->unique();
            $table->unsignedInteger('instansi_id')->nullable();
            $table->string('nama_logger')->nullable();
            $table->string('tabel_main')->nullable();
            $table->unsignedInteger('idlokasi')->nullable();
            $table->unsignedInteger('id_katlogger')->nullable();
            $table->unsignedTinyInteger('sensor_count')->nullable();
        });

        Schema::create('user_logger_access', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->string('logger_id', 15);
            $table->timestamps();
        });

        Schema::create('kategori_logger', function (Blueprint $table) {
            $table->increments('id_katlogger');
            $table->string('nama_kategori');
            $table->string('kode')->nullable();
        });

        Schema::create('parameter_sensor', function (Blueprint $table) {
            $table->increments('id_param');
            $table->string('logger_id', 15);
            $table->string('nama_parameter');
            $table->string('kolom_sensor');
            $table->string('satuan')->nullable();
            $table->string('parameter_utama')->nullable();
        });

        Schema::create('t_s16_01', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            $table->double('sensor1')->nullable();
        });

        Schema::create('temp_s16_latest', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            $table->double('sensor1')->nullable();
        });

        // Relasi temp16/19/50 di-eager-load bersamaan, jadi ketiga tabel latest
        // harus ada walau uji ini hanya mengisi keluarga 16.
        foreach (['temp_s19_latest', 'temp_s50_latest'] as $tabelLatest) {
            Schema::create($tabelLatest, function (Blueprint $table) {
                $table->increments('id');
                $table->string('id_logger', 15);
                $table->dateTime('waktu')->nullable();
            });
        }

        DB::table('kategori_logger')->insert([
            'id_katlogger' => 1, 'nama_kategori' => 'AWLR', 'kode' => 'AWLR',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ── Perkakas ────────────────────────────────────────────────────────────

    private function instansi(string $nama): int
    {
        return DB::table('instansi')->insertGetId(['nama' => $nama]);
    }

    private function user(string $username, string $level, ?int $instansiId, string $status = 'aktif'): t_User
    {
        $id = DB::table('t_user')->insertGetId([
            'nama' => strtoupper($username),
            'username' => $username,
            'password' => Hash::make('rahasia'),
            'level_user' => $level,
            'instansi_id' => $instansiId,
            'status' => $status,
        ]);

        return t_User::findOrFail($id);
    }

    private function logger(string $idLogger, ?int $instansiId, string $namaLokasi): void
    {
        $idLokasi = DB::table('t_lokasi')->insertGetId([
            'nama_lokasi' => $namaLokasi,
            'latitude' => '-7.797068',
            'longitude' => '110.370529',
        ]);

        DB::table('t_logger')->insert([
            'id_logger' => $idLogger,
            'instansi_id' => $instansiId,
            'nama_logger' => 'Logger ' . $idLogger,
            'tabel_main' => 't_s16_01',
            'idlokasi' => $idLokasi,
            'id_katlogger' => 1,
            'sensor_count' => 16,
        ]);

        DB::table('parameter_sensor')->insert([
            'logger_id' => $idLogger,
            'nama_parameter' => 'Tinggi Muka Air',
            'kolom_sensor' => 'sensor1',
            'satuan' => 'm',
            'parameter_utama' => 'tma',
        ]);
    }

    private function bacaan(string $idLogger, string $waktu, float $nilai, bool $latest = true): void
    {
        DB::table('t_s16_01')->insert([
            'id_logger' => $idLogger, 'waktu' => $waktu, 'sensor1' => $nilai,
        ]);

        if ($latest) {
            DB::table('temp_s16_latest')->updateOrInsert(
                ['id_logger' => $idLogger],
                ['waktu' => $waktu, 'sensor1' => $nilai],
            );
        }
    }

    private function grant(t_User $user, string $idLogger): void
    {
        DB::table('user_logger_access')->insert([
            'user_id' => $user->id_user,
            'logger_id' => $idLogger,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array<string, string> */
    private function basic(string $username, string $password = 'rahasia'): array
    {
        return ['Authorization' => 'Basic ' . base64_encode($username . ':' . $password)];
    }

    // ── Autentikasi ─────────────────────────────────────────────────────────

    public function test_tanpa_kredensial_ditolak(): void
    {
        $this->getJson('/api/integrasi/all_logger')
            ->assertStatus(401)
            ->assertJson(['status' => false])
            ->assertHeader('WWW-Authenticate', 'Basic realm="Mini-STESY Integrasi"');
    }

    public function test_password_salah_ditolak(): void
    {
        $this->user('pegawai', 'pegawai', $this->instansi('A'));

        $this->getJson('/api/integrasi/all_logger', $this->basic('pegawai', 'salah'))
            ->assertStatus(401)
            ->assertJson(['status' => false]);
    }

    public function test_akun_suspend_ditolak_walau_password_benar(): void
    {
        $instansi = $this->instansi('A');
        $user = $this->user('disuspend', 'pegawai', $instansi, 'suspend');
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($user, 'L1');

        $this->getJson('/api/integrasi/all_logger', $this->basic('disuspend'))
            ->assertStatus(401)
            ->assertJson(['status' => false]);
    }

    // ── Hak akses logger per user ───────────────────────────────────────────

    public function test_pegawai_hanya_melihat_logger_yang_diberikan(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);

        $this->logger('L1', $instansi, 'Pos Satu');
        $this->logger('L2', $instansi, 'Pos Dua');
        $this->grant($pegawai, 'L1');

        $response = $this->getJson('/api/integrasi/all_logger', $this->basic('pegawai'))->assertOk();

        // L2 satu instansi dengan pegawai, tapi tanpa grant tetap tidak terlihat.
        $this->assertSame(1, $response->json('jumlah_logger'));
        $this->assertSame(['L1'], $response->json('data.*.id_logger'));
    }

    public function test_instansi_admin_melihat_seluruh_logger_instansinya(): void
    {
        $instansi = $this->instansi('A');
        $lain = $this->instansi('B');
        $this->user('adminA', 'instansi_admin', $instansi);

        $this->logger('L1', $instansi, 'Pos Satu');
        $this->logger('L2', $instansi, 'Pos Dua');
        $this->logger('L3', $lain, 'Pos Instansi Lain');

        $response = $this->getJson('/api/integrasi/all_logger', $this->basic('adminA'))->assertOk();

        $this->assertSame(['L1', 'L2'], $response->json('data.*.id_logger'));
    }

    public function test_grant_lintas_instansi_ikut_terbawa(): void
    {
        $instansi = $this->instansi('A');
        $lain = $this->instansi('B');
        $admin = $this->user('adminA', 'instansi_admin', $instansi);

        $this->logger('L1', $instansi, 'Pos Satu');
        $this->logger('L3', $lain, 'Pos Instansi Lain');
        $this->grant($admin, 'L3');

        $response = $this->getJson('/api/integrasi/all_logger', $this->basic('adminA'))->assertOk();

        $this->assertSame(['L1', 'L3'], $response->json('data.*.id_logger'));
    }

    public function test_superadmin_melihat_semua_logger(): void
    {
        $this->user('super', 'superadmin', $this->instansi('A'));
        $this->logger('L1', $this->instansi('B'), 'Pos Satu');
        $this->logger('L2', null, 'Pos Tanpa Instansi');

        $response = $this->getJson('/api/integrasi/all_logger', $this->basic('super'))->assertOk();

        $this->assertSame(['L1', 'L2'], $response->json('data.*.id_logger'));
    }

    public function test_logger_di_luar_hak_akses_dijawab_tidak_terdaftar(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);

        $this->logger('L1', $instansi, 'Pos Satu');
        $this->logger('L2', $instansi, 'Pos Dua');
        $this->grant($pegawai, 'L1');
        $this->bacaan('L2', '2026-09-10 09:30:00', 1.5);

        // Pesan harus sama dengan id yang benar-benar tidak ada, supaya
        // keberadaan logger orang lain tidak terbaca dari luar.
        $milikOrangLain = $this->getJson('/api/integrasi?id_logger=L2', $this->basic('pegawai'));
        $tidakAda = $this->getJson('/api/integrasi?id_logger=XXX', $this->basic('pegawai'));

        $milikOrangLain->assertStatus(404)->assertJson(['status' => false, 'pesan' => 'Logger Tidak Terdaftar']);
        $tidakAda->assertStatus(404)->assertJson(['status' => false, 'pesan' => 'Logger Tidak Terdaftar']);
        $this->assertSame($tidakAda->json(), $milikOrangLain->json());
    }

    // ── Bentuk data ─────────────────────────────────────────────────────────

    public function test_riwayat_memakai_kunci_dari_nama_parameter(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');

        $this->bacaan('L1', '2026-09-10 09:00:00', 1.20, latest: false);
        $this->bacaan('L1', '2026-09-10 09:30:00', 1.35);

        $response = $this->getJson('/api/integrasi?id_logger=L1', $this->basic('pegawai'))->assertOk();

        $response->assertJson([
            'status' => true,
            'id_logger' => 'L1',
            'nama_lokasi' => 'Pos Satu',
            'jenis' => 'AWLR',
            'koneksi_logger' => 'On',
            'waktu_terakhir' => '2026-09-10 09:30:00',
            'jumlah_data' => 2,
            'parameter' => [
                ['nama_parameter' => 'Tinggi Muka Air', 'satuan' => 'm', 'kunci' => 'tinggi_muka_air'],
            ],
        ]);

        // Terbaru dulu, nilai sensor dipetakan ke kunci parameternya.
        $this->assertSame(1.35, $response->json('data.0.tinggi_muka_air'));
        $this->assertSame(1.20, $response->json('data.1.tinggi_muka_air'));
        $this->assertSame(-7.797068, $response->json('latitude'));
    }

    public function test_koneksi_off_kalau_data_terakhir_lewat_satu_jam(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');
        $this->bacaan('L1', '2026-09-10 08:00:00', 1.10);

        $this->getJson('/api/integrasi?id_logger=L1', $this->basic('pegawai'))
            ->assertOk()
            ->assertJson(['koneksi_logger' => 'Off']);
    }

    public function test_snapshot_all_logger_membawa_nilai_terakhir(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');
        $this->bacaan('L1', '2026-09-10 09:45:00', 2.75);

        $this->getJson('/api/integrasi/all_logger', $this->basic('pegawai'))
            ->assertOk()
            ->assertJson([
                'status' => true,
                'jumlah_logger' => 1,
                'data' => [[
                    'id_logger' => 'L1',
                    'nama_lokasi' => 'Pos Satu',
                    'jenis' => 'AWLR',
                    'koneksi_logger' => 'On',
                    'waktu' => '2026-09-10 09:45:00',
                    'data' => [
                        ['nama_parameter' => 'Tinggi Muka Air', 'satuan' => 'm', 'nilai' => 2.75],
                    ],
                ]],
            ]);
    }

    // ── Rentang tanggal ─────────────────────────────────────────────────────

    public function test_range_tanggal_menyaring_rentang(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');

        $this->bacaan('L1', '2026-09-01 08:00:00', 1.00, latest: false);
        $this->bacaan('L1', '2026-09-05 08:00:00', 2.00, latest: false);
        $this->bacaan('L1', '2026-09-09 08:00:00', 3.00);

        $response = $this->getJson(
            '/api/integrasi/range_tanggal?id_logger=L1&awal=2026-09-04&akhir=2026-09-05',
            $this->basic('pegawai'),
        )->assertOk();

        $this->assertSame(1, $response->json('jumlah_data'));
        // assertEquals, bukan assertSame: bilangan bulat seperti 2.0 dikirim
        // sebagai integer JSON, jadi tipenya int saat di-decode.
        $this->assertEquals(2.00, $response->json('data.0.tinggi_muka_air'));
    }

    public function test_range_tanggal_menolak_rentang_terlalu_lebar(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');

        $this->getJson(
            '/api/integrasi/range_tanggal?id_logger=L1&awal=2026-01-01&akhir=2026-06-30',
            $this->basic('pegawai'),
        )
            ->assertStatus(422)
            ->assertJson(['status' => false, 'pesan' => 'Rentang tanggal maksimal 31 hari.']);
    }

    public function test_range_tanggal_menerima_tepat_31_hari(): void
    {
        $instansi = $this->instansi('A');
        $pegawai = $this->user('pegawai', 'pegawai', $instansi);
        $this->logger('L1', $instansi, 'Pos Satu');
        $this->grant($pegawai, 'L1');

        $this->getJson(
            '/api/integrasi/range_tanggal?id_logger=L1&awal=2026-08-01&akhir=2026-08-31',
            $this->basic('pegawai'),
        )->assertOk();
    }

    public function test_range_tanggal_wajib_tanggal_valid(): void
    {
        $instansi = $this->instansi('A');
        $this->user('pegawai', 'pegawai', $instansi);

        $this->getJson('/api/integrasi/range_tanggal?id_logger=L1&awal=01-08-2026&akhir=2026-08-31', $this->basic('pegawai'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('awal');

        $this->getJson('/api/integrasi/range_tanggal?id_logger=L1&awal=2026-08-31&akhir=2026-08-01', $this->basic('pegawai'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('akhir');
    }
}
