<?php

namespace Tests\Feature;

use App\Support\ArrRainStatus;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArrRainStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time mid-hour, mid-day to avoid hour/day boundary flakiness.
        Carbon::setTestNow(Carbon::parse('2026-06-17 10:30:00'));

        Schema::dropIfExists('t_s16_99');
        Schema::dropIfExists('klasifikasi_hujan');
        Schema::dropIfExists('klasifikasi_threshold');

        Schema::create('klasifikasi_threshold', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('id_kategori');
            $table->string('state_key');
            $table->string('state_label');
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
        });

        Schema::create('t_s16_99', function (Blueprint $table) {
            $table->increments('id');
            $table->string('id_logger', 15);
            $table->dateTime('waktu');
            $table->double('sensor1')->nullable();
        });

        Schema::create('klasifikasi_hujan', function (Blueprint $table) {
            $table->increments('id_klasifikasi');
            $table->string('logger_id', 15);
            $table->string('waktu', 10);
            $table->string('debit_air', 255);
            $table->string('intensitas', 255);
            $table->tinyInteger('status')->default(1);
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function seedReadings(): void
    {
        DB::table('t_s16_99')->insert([
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:15:00', 'sensor1' => 5],   // current hour
            ['id_logger' => '10002', 'waktu' => '2026-06-17 10:45:00', 'sensor1' => 3],   // current hour
            ['id_logger' => '10002', 'waktu' => '2026-06-17 08:00:00', 'sensor1' => 10],  // same day, earlier hour
            ['id_logger' => '10002', 'waktu' => '2026-06-16 10:15:00', 'sensor1' => 100], // yesterday
        ]);
    }

    private function seedPerjamThresholds(int $status = 1): void
    {
        $rows = [
            ['Tidak Hujan', 0],
            ['Hujan Sangat Ringan', 0.1],
            ['Hujan Ringan', 1],
            ['Hujan Sedang', 5],
            ['Hujan Lebat', 10],
            ['Hujan Sangat Lebat', 20],
        ];
        foreach ($rows as $r) {
            DB::table('klasifikasi_hujan')->insert([
                'logger_id' => '10002', 'waktu' => 'perjam',
                'debit_air' => (string) $r[1], 'intensitas' => $r[0], 'status' => $status,
            ]);
        }
    }

    public function test_hourly_accumulation_sums_only_current_hour(): void
    {
        $this->seedReadings();

        $this->assertSame(8.0, ArrRainStatus::hourlyAccumulation('t_s16_99', '10002', 'sensor1'));
    }

    public function test_daily_accumulation_sums_only_current_day(): void
    {
        $this->seedReadings();

        $this->assertSame(18.0, ArrRainStatus::dailyAccumulation('t_s16_99', '10002', 'sensor1'));
    }

    public function test_can_query_rain_table_validates_columns(): void
    {
        $this->assertTrue(ArrRainStatus::canQueryRainTable('t_s16_99', 'sensor1'));
        $this->assertFalse(ArrRainStatus::canQueryRainTable('t_s16_99', 'sensor_missing'));
        $this->assertFalse(ArrRainStatus::canQueryRainTable('table_does_not_exist', 'sensor1'));
        $this->assertFalse(ArrRainStatus::canQueryRainTable('bad name;', 'sensor1'));
    }

    public function test_classify_returns_highest_matching_intensity(): void
    {
        $this->seedPerjamThresholds();

        $this->assertSame('Hujan Sedang', ArrRainStatus::classify('10002', 'perjam', 8.0));
        $this->assertSame('Hujan Sangat Lebat', ArrRainStatus::classify('10002', 'perjam', 25.0));
        $this->assertSame('Tidak Hujan', ArrRainStatus::classify('10002', 'perjam', 0.05));
        $this->assertNull(ArrRainStatus::classify('10002', 'perjam', null));
    }

    public function test_notif_enabled_reflects_status_column(): void
    {
        $this->seedPerjamThresholds(status: 1);
        $this->assertTrue(ArrRainStatus::notifEnabled('10002'));

        DB::table('klasifikasi_hujan')->where('logger_id', '10002')->update(['status' => 0]);
        $this->assertFalse(ArrRainStatus::notifEnabled('10002'));

        $this->assertFalse(ArrRainStatus::notifEnabled('99999')); // no rows
    }

    public function test_notif_enabled_is_resilient_when_status_column_is_absent(): void
    {
        // Simulates prod before the add-status migration has run: ingestion must
        // not crash, and ARR notifications keep firing (treated as enabled).
        Schema::table('klasifikasi_hujan', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        DB::table('klasifikasi_hujan')->insert([
            'logger_id' => '10002', 'waktu' => 'perjam', 'debit_air' => '5', 'intensitas' => 'Hujan Sedang',
        ]);

        $this->assertTrue(ArrRainStatus::notifEnabled('10002'));
    }

    private function seedCategoryThresholds(): void
    {
        DB::table('klasifikasi_threshold')->insert([
            ['id_kategori' => 2, 'state_key' => 'tidak_hujan',         'state_label' => 'Tidak Hujan',         'min_value' => null, 'max_value' => 0.10, 'sort_order' => 1],
            ['id_kategori' => 2, 'state_key' => 'hujan_sangat_ringan', 'state_label' => 'Hujan Sangat Ringan', 'min_value' => 0.10, 'max_value' => 1.00, 'sort_order' => 2],
            ['id_kategori' => 2, 'state_key' => 'hujan_ringan',        'state_label' => 'Hujan Ringan',        'min_value' => 1.00, 'max_value' => 2.50, 'sort_order' => 3],
            ['id_kategori' => 2, 'state_key' => 'koneksi_terputus',    'state_label' => 'Koneksi Terputus',    'min_value' => null, 'max_value' => null, 'sort_order' => 9],
        ]);
    }

    public function test_category_state_label_and_key_match_value_range(): void
    {
        $this->seedCategoryThresholds();

        $this->assertSame('Hujan Ringan', ArrRainStatus::categoryStateLabel(2, 1.00));
        $this->assertSame('hujan_ringan', ArrRainStatus::categoryStateKey(2, 1.00));
        $this->assertSame('Tidak Hujan', ArrRainStatus::categoryStateLabel(2, 0.0));
    }

    public function test_category_label_is_null_without_thresholds_or_value(): void
    {
        $this->assertNull(ArrRainStatus::categoryStateLabel(2, 1.00)); // no rows seeded
        $this->seedCategoryThresholds();
        $this->assertNull(ArrRainStatus::categoryStateLabel(2, null));
        $this->assertNull(ArrRainStatus::categoryStateLabel(null, 1.00));
    }

    public function test_is_alert_intensity_only_for_sedang_and_above(): void
    {
        $this->assertTrue(ArrRainStatus::isAlertIntensity('Hujan Sedang'));
        $this->assertTrue(ArrRainStatus::isAlertIntensity('Hujan Lebat'));
        $this->assertTrue(ArrRainStatus::isAlertIntensity('Hujan Sangat Lebat'));

        $this->assertFalse(ArrRainStatus::isAlertIntensity('Hujan Ringan'));
        $this->assertFalse(ArrRainStatus::isAlertIntensity('Hujan Sangat Ringan'));
        $this->assertFalse(ArrRainStatus::isAlertIntensity('Tidak Hujan'));
        $this->assertFalse(ArrRainStatus::isAlertIntensity(null));
    }
}
