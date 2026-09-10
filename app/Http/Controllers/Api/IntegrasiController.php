<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Support\SensorFamily;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * API integrasi Mini-STESY: tiga endpoint untuk pihak luar menarik data
 * logger, mengikuti bentuk API Sisda di be_jastir2.
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
     * @return array<int, array{kunci: string, kolom: string, nama_parameter: string, satuan: ?string}>
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

    /** @param \Illuminate\Support\Collection<int, object> $baris */
    private function bungkusRiwayat(t_Logger $logger, $baris): array
    {
        $parameter = $this->parameter($logger);
        [$koneksi, $waktuTerakhir] = $this->koneksi($logger);

        $data = $baris->map(function (object $row) use ($parameter) {
            $item = ['waktu' => $row->waktu ?? null];

            foreach ($parameter as $p) {
                $item[$p['kunci']] = $this->angka($row->{$p['kolom']} ?? null);
            }

            return $item;
        })->values();

        return [
            'status'         => true,
            'id_logger'      => $logger->id_logger,
            'nama_lokasi'    => $logger->nama_pos,
            'jenis'          => $logger->kategori?->nama_kategori,
            'latitude'       => $this->angka($logger->lokasi?->latitude),
            'longitude'      => $this->angka($logger->lokasi?->longitude),
            'koneksi_logger' => $koneksi,
            'waktu_terakhir' => $waktuTerakhir,
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
