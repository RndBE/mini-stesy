<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Support\FaultStatus;
use App\Support\SensorFamily;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * API integrasi Mini-STESY: endpoint untuk pihak luar menarik data logger.
 * Tiga yang pertama mengikuti bentuk API Sisda di be_jastir2; `agregat`
 * menambahkan riwayat yang sudah diringkas per bucket waktu.
 *
 * Bedanya dengan Sisda: autentikasinya per user (Basic Auth ke `t_user`),
 * bukan satu kredensial bersama, sehingga cakupan data tiap pemanggil
 * dibatasi hak akses loggernya lewat t_Logger::scopeForUser().
 */
class IntegrasiController extends Controller
{
    /** Baris riwayat terbaru yang dikembalikan endpoint riwayat. */
    private const BATAS_RIWAYAT = 300;

    /** Rentang tanggal terlebar yang dilayani dalam satu panggilan. */
    private const MAKS_HARI = 31;

    /** Batas baris untuk permintaan rentang tanggal. */
    private const BATAS_RENTANG = 5000;

    /** Data lebih lama dari ini membuat logger dianggap terputus. */
    private const AMBANG_ONLINE_MENIT = 60;

    /** Interval bucket yang dilayani endpoint agregat, dalam menit. */
    private const INTERVAL_MENIT = ['5m' => 5, '15m' => 15, '1h' => 60, '1d' => 1440];

    /**
     * Rentang terlebar per interval, dalam hari, supaya satu panggilan tidak
     * melebihi ±3000 bucket.
     */
    private const MAKS_HARI_AGREGAT = ['5m' => 7, '15m' => 31, '1h' => 92, '1d' => 366];

    /**
     * GET /api/integrasi?id_logger=...
     * Riwayat terbaru satu logger (maksimal 300 baris, terbaru dulu).
     */
    public function index(Request $request): JsonResponse
    {
        $logger = $this->cariLogger($request);

        if (! $logger) {
            return $this->tidakTerdaftar();
        }

        return response()->json(
            $this->bungkusRiwayat($logger, $this->baris($logger, null, null, self::BATAS_RIWAYAT))
        );
    }

    /**
     * GET /api/integrasi/all_logger
     * Snapshot nilai terakhir seluruh logger yang boleh diakses pemanggil.
     */
    public function allLogger(Request $request): JsonResponse
    {
        $loggers = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'params', 'temp16', 'temp19', 'temp50'])
            ->orderBy('id_logger')
            ->get();

        $data = $loggers->map(function (t_Logger $logger) {
            [$koneksi, $waktu] = $this->koneksi($logger);
            $snapshot = $this->snapshot($logger);

            return [
                'id_logger'      => $logger->id_logger,
                'nama_lokasi'    => $logger->nama_pos,
                'jenis'          => $logger->kategori?->nama_kategori,
                'latitude'       => $this->angka($logger->lokasi?->latitude),
                'longitude'      => $this->angka($logger->lokasi?->longitude),
                'koneksi_logger' => $koneksi,
                'waktu'          => $waktu,
                'data'           => array_map(fn (array $p) => [
                    'nama_parameter' => $p['nama_parameter'],
                    'satuan'         => $p['satuan'],
                    'nilai'          => $this->angka($snapshot->{$p['kolom']} ?? null),
                ], $this->parameter($logger)),
            ];
        })->values();

        return response()->json([
            'status'        => true,
            'jumlah_logger' => $data->count(),
            'data'          => $data,
        ]);
    }

    /**
     * GET /api/integrasi/range_tanggal?id_logger=...&awal=Y-m-d&akhir=Y-m-d
     * Riwayat satu logger pada rentang tanggal tertentu.
     */
    public function rangeTanggal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_logger' => ['required', 'string'],
            'awal'      => ['required', 'date_format:Y-m-d'],
            'akhir'     => ['required', 'date_format:Y-m-d', 'after_or_equal:awal'],
        ]);

        $awal  = Carbon::parse($validated['awal'])->startOfDay();
        $akhir = Carbon::parse($validated['akhir'])->endOfDay();

        // Dibatasi karena endpoint ini membaca tabel histori produksi langsung.
        if ($awal->diffInDays($akhir) >= self::MAKS_HARI) {
            return response()->json([
                'status' => false,
                'pesan'  => 'Rentang tanggal maksimal ' . self::MAKS_HARI . ' hari.',
            ], 422);
        }

        $logger = $this->cariLogger($request);

        if (! $logger) {
            return $this->tidakTerdaftar();
        }

        return response()->json(
            $this->bungkusRiwayat($logger, $this->baris($logger, $awal, $akhir, self::BATAS_RENTANG))
        );
    }

    /**
     * GET /api/integrasi/agregat?id_logger=...&awal=Y-m-d&akhir=Y-m-d&interval=1h
     * Riwayat satu logger yang diringkas per bucket waktu, terlama dulu:
     * rata-rata, minimum dan maksimum tiap parameter, plus jumlah baris per
     * bucket (dipakai pemanggil untuk menghitung kelengkapan data). Parameter
     * fault digabung dengan bitwise OR, sama seperti halaman Analisa.
     */
    public function agregat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_logger' => ['required', 'string'],
            'awal'      => ['required', 'date_format:Y-m-d'],
            'akhir'     => ['required', 'date_format:Y-m-d', 'after_or_equal:awal'],
            'interval'  => ['required', Rule::in(array_keys(self::INTERVAL_MENIT))],
        ]);

        $interval = $validated['interval'];
        $awal     = Carbon::parse($validated['awal'])->startOfDay();
        $akhir    = Carbon::parse($validated['akhir'])->endOfDay();
        $maksHari = self::MAKS_HARI_AGREGAT[$interval];

        // Agregasinya jalan di tabel histori produksi: interval kecil hanya
        // untuk rentang pendek.
        if ($awal->diffInDays($akhir) >= $maksHari) {
            return response()->json([
                'status' => false,
                'pesan'  => "Rentang tanggal maksimal {$maksHari} hari untuk interval {$interval}.",
            ], 422);
        }

        $logger = $this->cariLogger($request);

        if (! $logger) {
            return $this->tidakTerdaftar();
        }

        $tabel = $this->tabelUtama($logger);

        // Kolom masuk ke ekspresi SQL mentah, jadi hanya yang benar-benar ada
        // di tabel dan bernama aman.
        $kolomAda  = array_flip(Schema::getColumnListing($tabel));
        $parameter = array_values(array_filter(
            $this->parameter($logger),
            fn (array $p) => isset($kolomAda[$p['kolom']]) && preg_match('/^[A-Za-z0-9_]+$/', $p['kolom'])
        ));

        $bucket = $this->ekspresiBucket(self::INTERVAL_MENIT[$interval]);
        $pilih  = ["{$bucket} AS bucket", 'COUNT(*) AS jumlah_data'];

        foreach ($parameter as $i => $p) {
            $kolom   = "`{$p['kolom']}`";
            $pilih[] = $p['fault']
                // Nilai unik per bucket lalu di-OR di PHP: BIT_OR tidak ada di SQLite.
                ? "GROUP_CONCAT(DISTINCT {$kolom}) AS f{$i}"
                : "AVG({$kolom}) AS rata{$i}, MIN({$kolom}) AS min{$i}, MAX({$kolom}) AS maks{$i}";
        }

        $baris = DB::table($tabel)
            ->selectRaw(implode(', ', $pilih))
            ->where('id_logger', $logger->id_logger)
            ->whereBetween('waktu', [$awal, $akhir])
            ->groupBy(DB::raw($bucket))
            ->orderBy(DB::raw($bucket))
            ->get();

        $data = $baris->map(function (object $row) use ($parameter) {
            $item = ['waktu' => (string) $row->bucket, 'jumlah_data' => (int) $row->jumlah_data];

            foreach ($parameter as $i => $p) {
                if ($p['fault']) {
                    $nilai = $row->{"f{$i}"} ?? null;
                    $item[$p['kunci']] = ['bit_or' => $nilai === null ? null : FaultStatus::combine(explode(',', (string) $nilai))];
                    continue;
                }

                $item[$p['kunci']] = [
                    'rata' => $this->angka($row->{"rata{$i}"} ?? null),
                    'min'  => $this->angka($row->{"min{$i}"} ?? null),
                    'maks' => $this->angka($row->{"maks{$i}"} ?? null),
                ];
            }

            return $item;
        })->values();

        return response()->json($this->identitas($logger) + [
            'interval'      => $interval,
            'awal'          => $awal->format('Y-m-d H:i:s'),
            'akhir'         => $akhir->format('Y-m-d H:i:s'),
            'parameter'     => array_map(fn (array $p) => [
                'nama_parameter' => $p['nama_parameter'],
                'satuan'         => $p['satuan'],
                'kunci'          => $p['kunci'],
                'agregasi'       => $p['fault'] ? 'bit_or' : 'rata_min_maks',
            ], $parameter),
            'jumlah_bucket' => $data->count(),
            'data'          => $data,
        ]);
    }

    /**
     * Awal bucket sebagai teks `Y-m-d H:i:s`. Kolom `waktu` sudah disimpan
     * dalam waktu lokal, jadi bucket harian mulai dari tengah malam WIB.
     * SQLite hanya dipakai test; produksi MySQL.
     */
    private function ekspresiBucket(int $menit): string
    {
        $sqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($menit >= 1440) {
            return $sqlite ? "strftime('%Y-%m-%d 00:00:00', waktu)" : "DATE_FORMAT(waktu, '%Y-%m-%d 00:00:00')";
        }

        if ($menit >= 60) {
            return $sqlite ? "strftime('%Y-%m-%d %H:00:00', waktu)" : "DATE_FORMAT(waktu, '%Y-%m-%d %H:00:00')";
        }

        return $sqlite
            ? "strftime('%Y-%m-%d %H:', waktu) || printf('%02d', (CAST(strftime('%M', waktu) AS INTEGER) / {$menit}) * {$menit}) || ':00'"
            : "CONCAT(DATE_FORMAT(waktu, '%Y-%m-%d %H:'), LPAD(FLOOR(MINUTE(waktu) / {$menit}) * {$menit}, 2, '0'), ':00')";
    }

    /**
     * Logger yang diminta, disaring hak akses pemanggil. Logger milik orang
     * lain mengembalikan null, sama seperti id yang tidak terdaftar.
     */
    private function cariLogger(Request $request): ?t_Logger
    {
        $id = trim((string) $request->query('id_logger', ''));

        if ($id === '') {
            return null;
        }

        return t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'params', 'temp16', 'temp19', 'temp50'])
            ->where('id_logger', $id)
            ->first();
    }

    /**
     * Daftar parameter logger beserta kunci yang dipakai di baris riwayat.
     * Nama parameter yang kembar dibedakan dengan kolom sensornya supaya
     * tidak saling menimpa di keluaran.
     *
     * @return array<int, array{kunci: string, kolom: string, nama_parameter: string, satuan: ?string, fault: bool}>
     */
    private function parameter(t_Logger $logger): array
    {
        $hasil = [];
        $terpakai = [];

        foreach ($logger->params as $p) {
            $kolom = trim((string) $p->kolom_sensor);

            if ($kolom === '') {
                continue;
            }

            $kunci = Str::slug((string) $p->nama_parameter, '_') ?: $kolom;

            if (isset($terpakai[$kunci])) {
                $kunci .= '_' . $kolom;
            }
            $terpakai[$kunci] = true;

            $hasil[] = [
                'kunci'          => $kunci,
                'kolom'          => $kolom,
                'nama_parameter' => (string) $p->nama_parameter,
                'satuan'         => $p->satuan,
                'fault'          => FaultStatus::isFaultParam($p),
            ];
        }

        return $hasil;
    }

    /**
     * Baris riwayat, terbaru dulu. Tanpa rentang tanggal, ambil sejumlah
     * baris terakhir apa adanya.
     */
    private function baris(t_Logger $logger, ?Carbon $awal, ?Carbon $akhir, int $batas)
    {
        $query = DB::table($this->tabelUtama($logger))
            ->where('id_logger', $logger->id_logger)
            ->orderByDesc('waktu')
            ->limit($batas);

        if ($awal && $akhir) {
            $query->whereBetween('waktu', [$awal, $akhir]);
        }

        return $query->get();
    }

    /**
     * Tabel histori logger. Kalau `tabel_main` kosong atau bukan tabel
     * keluarga sensor yang dikenal, jatuh ke shard pertama keluarga yang
     * sesuai jumlah sensornya — sama seperti AnalisaApiController.
     */
    private function tabelUtama(t_Logger $logger): string
    {
        $tabel = trim((string) $logger->tabel_main);

        if (SensorFamily::isFamilyTable($tabel)) {
            return $tabel;
        }

        return SensorFamily::mainTablePrefix(
            SensorFamily::familyFor((int) ($logger->sensor_count ?? 0))
        ) . '01';
    }

    /** Baris snapshot terbaru dari tabel latest keluarga mana pun. */
    private function snapshot(t_Logger $logger): object
    {
        return collect([$logger->temp16, $logger->temp19, $logger->temp50])
            ->filter(fn ($row) => $row && ! empty($row->waktu))
            ->sortByDesc(fn ($row) => (string) $row->waktu)
            ->first() ?? new \stdClass();
    }

    /**
     * Status koneksi dan waktu data terakhir, memakai ambang 60 menit yang
     * sama dengan dashboard dan mobile API.
     *
     * @return array{0: string, 1: ?string}
     */
    private function koneksi(t_Logger $logger): array
    {
        $waktu = collect([$logger->temp16, $logger->temp19, $logger->temp50])
            ->filter(fn ($row) => $row && ! empty($row->waktu))
            ->map(fn ($row) => (string) $row->waktu)
            ->sortDesc()
            ->first();

        if (! $waktu) {
            return ['Off', null];
        }

        $selisih = Carbon::parse($waktu)->diffInMinutes(now());

        return [$selisih < self::AMBANG_ONLINE_MENIT ? 'On' : 'Off', $waktu];
    }

    /** Kepala respons riwayat dan agregat: identitas logger dan status koneksinya. */
    private function identitas(t_Logger $logger): array
    {
        [$koneksi, $waktuTerakhir] = $this->koneksi($logger);

        return [
            'status'         => true,
            'id_logger'      => $logger->id_logger,
            'nama_lokasi'    => $logger->nama_pos,
            'jenis'          => $logger->kategori?->nama_kategori,
            'latitude'       => $this->angka($logger->lokasi?->latitude),
            'longitude'      => $this->angka($logger->lokasi?->longitude),
            'koneksi_logger' => $koneksi,
            'waktu_terakhir' => $waktuTerakhir,
        ];
    }

    /** @param \Illuminate\Support\Collection<int, object> $baris */
    private function bungkusRiwayat(t_Logger $logger, $baris): array
    {
        $parameter = $this->parameter($logger);

        $data = $baris->map(function (object $row) use ($parameter) {
            $item = ['waktu' => $row->waktu ?? null];

            foreach ($parameter as $p) {
                $item[$p['kunci']] = $this->angka($row->{$p['kolom']} ?? null);
            }

            return $item;
        })->values();

        return $this->identitas($logger) + [
            'parameter'      => array_map(fn (array $p) => [
                'nama_parameter' => $p['nama_parameter'],
                'satuan'         => $p['satuan'],
                'kunci'          => $p['kunci'],
            ], $parameter),
            'jumlah_data'    => $data->count(),
            'data'           => $data,
        ];
    }

    private function angka($nilai): ?float
    {
        return is_numeric($nilai) ? (float) $nilai : null;
    }

    /**
     * Dipakai untuk id yang tidak ada maupun logger di luar hak akses
     * pemanggil. Pesannya sengaja sama supaya keberadaan logger milik
     * instansi lain tidak terbaca dari luar.
     */
    private function tidakTerdaftar(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'pesan'  => 'Logger Tidak Terdaftar',
        ], 404);
    }
}
