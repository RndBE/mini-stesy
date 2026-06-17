<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use App\Models\TingkatSiagaAwlr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Unified "Tingkat Siaga" page for AWLR (water-level alert levels) and
 * ARR (BMKG rain classification, per-jam & per-hari accumulation in mm).
 * The same list/route serves both; behaviour branches on the logger type.
 */
class TingkatSiagaAwlrController extends Controller
{
    /**
     * Canonical BMKG rain intensities for ARR. Label & colour are server-owned
     * (not editable from the client); only the accumulation value (mm) per
     * period is configurable. Default values follow the BMKG reference.
     */
    private const ARR_INTENSITIES = [
        ['nama' => 'Tidak Hujan',         'warna' => '#22C55E', 'perjam' => 0,   'perhari' => 0],
        ['nama' => 'Hujan Sangat Ringan', 'warna' => '#38BDF8', 'perjam' => 0.1, 'perhari' => 0.1],
        ['nama' => 'Hujan Ringan',        'warna' => '#2563EB', 'perjam' => 1,   'perhari' => 5],
        ['nama' => 'Hujan Sedang',        'warna' => '#FACC15', 'perjam' => 5,   'perhari' => 20],
        ['nama' => 'Hujan Lebat',         'warna' => '#F97316', 'perjam' => 10,  'perhari' => 50],
        ['nama' => 'Hujan Sangat Lebat',  'warna' => '#EF4444', 'perjam' => 20,  'perhari' => 100],
    ];

    private const ARR_PERIODS = ['perjam', 'perhari'];

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $loggers = $this->loggersQuery()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('id_logger', 'like', '%' . $q . '%')
                        ->orWhere('nama_logger', 'like', '%' . $q . '%')
                        ->orWhereHas('lokasi', function ($lokasiQ) use ($q) {
                            $lokasiQ->where('nama_lokasi', 'like', '%' . $q . '%');
                        });
                });
            })
            ->orderBy('id_logger')
            ->get();

        return view('tingkat-siaga-awlr.index', [
            'title' => 'Tingkat Siaga',
            'rows' => $this->buildRows($loggers),
            'q' => $q,
        ]);
    }

    public function update(Request $request, string $idLogger)
    {
        $logger = $this->loggersQuery()
            ->where('id_logger', $idLogger)
            ->firstOrFail();

        return $this->isArr($logger)
            ? $this->updateArr($request, $logger)
            : $this->updateAwlr($request, $logger);
    }

    // ── AWLR ────────────────────────────────────────────────────────────────

    private function updateAwlr(Request $request, t_Logger $logger)
    {
        $validated = $request->validate([
            'status_notifikasi' => ['required', 'boolean'],
            'jeda_notif' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'levels' => ['nullable', 'array'],
            'levels.*.nama' => ['required_with:levels', 'string', 'max:50'],
            'levels.*.nilai' => ['required_with:levels', 'numeric'],
            'levels.*.warna' => ['required_with:levels', 'regex:/^#([0-9A-Fa-f]{6})$/'],
        ]);

        $isActive = (bool) ($validated['status_notifikasi'] ?? false);
        $levels = collect($validated['levels'] ?? [])->values();

        if ($isActive && $levels->isEmpty()) {
            throw ValidationException::withMessages([
                'levels' => 'Level alert wajib diisi ketika status notifikasi aktif.',
            ]);
        }

        DB::transaction(function () use ($logger, $isActive, $validated, $levels) {
            TingkatSiagaAwlr::query()
                ->where('id_logger', $logger->id_logger)
                ->update(['status' => 0]);

            if ($isActive) {
                $logger->jeda_notif = (int) ($validated['jeda_notif'] ?? $logger->jeda_notif ?? 1);
                $logger->save();

                foreach ($levels as $index => $levelData) {
                    $statusOrder = $index + 1;

                    $level = TingkatSiagaAwlr::query()
                        ->where('id_logger', $logger->id_logger)
                        ->where('id_status', $statusOrder)
                        ->first();

                    if (!$level) {
                        $level = new TingkatSiagaAwlr();
                        $level->id_logger = $logger->id_logger;
                        $level->id_status = $statusOrder;
                    }

                    $level->nama = trim((string) $levelData['nama']);
                    $level->nilai = (float) $levelData['nilai'];
                    $level->warna = strtoupper((string) $levelData['warna']);
                    $level->status = 1;
                    $level->save();
                }
            }
        });

        return $this->freshRowResponse($logger->id_logger);
    }

    // ── ARR ───────────────────────────────────────────────────────────────--

    private function updateArr(Request $request, t_Logger $logger)
    {
        $validated = $request->validate([
            'status_notifikasi' => ['required', 'boolean'],
            'jeda_notif' => ['nullable', 'integer', 'min:1', 'max:1440'],
            'klasifikasi' => ['nullable', 'array'],
            'klasifikasi.perjam' => ['nullable', 'array'],
            'klasifikasi.perhari' => ['nullable', 'array'],
            'klasifikasi.*.*.debit_air' => ['nullable', 'numeric', 'min:0'],
        ]);

        $isActive = (bool) ($validated['status_notifikasi'] ?? false);

        // Preserve existing values when the client omits a period.
        $existing = DB::table('klasifikasi_hujan')
            ->where('logger_id', $logger->id_logger)
            ->get()
            ->keyBy(fn ($row) => $row->waktu . '|' . $row->intensitas);

        DB::transaction(function () use ($request, $logger, $isActive, $validated, $existing) {
            foreach (self::ARR_PERIODS as $period) {
                $values = $request->input("klasifikasi.$period");

                foreach (self::ARR_INTENSITIES as $index => $intensity) {
                    $payloadValue = $values[$index]['debit_air'] ?? null;
                    $current = $existing->get($period . '|' . $intensity['nama']);

                    if (is_numeric($payloadValue)) {
                        $debit = (float) $payloadValue;
                    } elseif ($current && is_numeric($current->debit_air)) {
                        $debit = (float) $current->debit_air; // preserve
                    } else {
                        $debit = (float) $intensity[$period];  // first-time default
                    }

                    DB::table('klasifikasi_hujan')->updateOrInsert(
                        [
                            'logger_id' => $logger->id_logger,
                            'waktu' => $period,
                            'intensitas' => $intensity['nama'],
                        ],
                        [
                            'debit_air' => (string) $debit,
                            'status' => $isActive ? 1 : 0,
                        ]
                    );
                }
            }

            if ($isActive) {
                $logger->jeda_notif = (int) ($validated['jeda_notif'] ?? $logger->jeda_notif ?? 1);
                $logger->save();
            }
        });

        return $this->freshRowResponse($logger->id_logger);
    }

    // ── Shared ──────────────────────────────────────────────────────────────

    private function freshRowResponse(string $idLogger)
    {
        $fresh = $this->loggersQuery()->where('id_logger', $idLogger)->firstOrFail();
        $row = $this->buildRows(collect([$fresh]))->first();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan notifikasi berhasil disimpan.',
            'row' => $row,
        ]);
    }

    private function loggersQuery()
    {
        return t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'kategori'])
            ->where(function ($query) {
                $query->whereIn('id_katlogger', [1, 2])
                    ->orWhereHas('kategori', function ($kategoriQ) {
                        $kategoriQ->whereRaw('LOWER(nama_kategori) IN (?, ?)', ['awlr', 'arr']);
                    });
            });
    }

    private function isArr($logger): bool
    {
        if ((int) ($logger->id_katlogger ?? 0) === 2) {
            return true;
        }

        return strtolower((string) optional($logger->kategori)->nama_kategori) === 'arr';
    }

    private function buildRows($loggers)
    {
        $awlrIds = $loggers->reject(fn ($l) => $this->isArr($l))->pluck('id_logger');
        $arrIds = $loggers->filter(fn ($l) => $this->isArr($l))->pluck('id_logger');

        $levelsByLogger = TingkatSiagaAwlr::query()
            ->whereIn('id_logger', $awlrIds)
            ->orderBy('id_status')
            ->orderBy('nilai')
            ->get()
            ->groupBy('id_logger');

        $hujanByLogger = DB::table('klasifikasi_hujan')
            ->whereIn('logger_id', $arrIds)
            ->get()
            ->groupBy('logger_id');

        return $loggers->map(function ($logger) use ($levelsByLogger, $hujanByLogger) {
            return $this->isArr($logger)
                ? $this->buildArrRow($logger, collect($hujanByLogger->get($logger->id_logger) ?? []))
                : $this->buildAwlrRow($logger, ($levelsByLogger->get($logger->id_logger) ?? collect()));
        })->values();
    }

    private function buildAwlrRow($logger, $allLevels): array
    {
        $levels = $allLevels->where('status', 1)->values();
        $isNotifActive = $levels->isNotEmpty();
        $namaLokasi = optional($logger->lokasi)->nama_lokasi;

        return [
            'id_logger' => $logger->id_logger,
            'tipe' => 'AWLR',
            'nama_pos' => $logger->nama_logger ?: $namaLokasi,
            'nama_lokasi' => $namaLokasi,
            'status_notifikasi' => $isNotifActive ? 'Aktif' : 'Tidak Aktif',
            'status_notifikasi_bool' => $isNotifActive,
            'jeda_notif' => $isNotifActive && $logger->jeda_notif !== null
                ? (int) $logger->jeda_notif . ' menit'
                : '-',
            'jeda_notif_value' => $logger->jeda_notif !== null ? (int) $logger->jeda_notif : 1,
            'levels' => $levels->map(fn ($level) => [
                'nama' => $level->nama,
                'nilai' => $this->trimNumber($level->nilai),
                'warna' => (string) $level->warna,
            ])->all(),
            'klasifikasi' => null,
        ];
    }

    private function buildArrRow($logger, $hujanRows): array
    {
        $byKey = $hujanRows->keyBy(fn ($r) => $r->waktu . '|' . $r->intensitas);
        $namaLokasi = optional($logger->lokasi)->nama_lokasi;

        $klasifikasi = [];
        foreach (self::ARR_PERIODS as $period) {
            $klasifikasi[$period] = collect(self::ARR_INTENSITIES)->map(function ($intensity) use ($byKey, $period) {
                $existing = $byKey->get($period . '|' . $intensity['nama']);
                $nilai = $existing && is_numeric($existing->debit_air)
                    ? (float) $existing->debit_air
                    : (float) $intensity[$period];

                return [
                    'intensitas' => $intensity['nama'],
                    'warna' => $intensity['warna'],
                    'nilai' => $this->trimNumber($nilai),
                ];
            })->all();
        }

        $isNotifActive = $hujanRows->contains(fn ($r) => (int) ($r->status ?? 0) === 1);

        return [
            'id_logger' => $logger->id_logger,
            'tipe' => 'ARR',
            'nama_pos' => $logger->nama_logger ?: $namaLokasi,
            'nama_lokasi' => $namaLokasi,
            'status_notifikasi' => $isNotifActive ? 'Aktif' : 'Tidak Aktif',
            'status_notifikasi_bool' => $isNotifActive,
            'jeda_notif' => $isNotifActive && $logger->jeda_notif !== null
                ? (int) $logger->jeda_notif . ' menit'
                : '-',
            'jeda_notif_value' => $logger->jeda_notif !== null ? (int) $logger->jeda_notif : 1,
            'levels' => [],
            'klasifikasi' => $klasifikasi,
        ];
    }

    private function trimNumber($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }
}
