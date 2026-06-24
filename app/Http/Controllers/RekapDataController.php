<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\t_Logger;
use App\Support\DataMasukStats;
use App\Support\SensorFamily;

class RekapDataController extends Controller
{
    public function index()
    {
        return view('rekap-data.index', ['title' => 'Rekap Data']);
    }

    public function getData(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate   = $request->query('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['success' => false, 'message' => 'Start date dan end date harus diisi.'], 400);
        }

        try {
            $start = Carbon::createFromFormat('Y-m-d', $startDate, config('app.timezone'))->startOfDay();
            $end   = Carbon::createFromFormat('Y-m-d', $endDate,   config('app.timezone'))->endOfDay();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Format tanggal tidak valid.'], 400);
        }

        if ($start->gt($end)) {
            return response()->json(['success' => false, 'message' => 'Start date harus sebelum end date.'], 400);
        }

        if ($start->diffInDays($end) > 30) {
            return response()->json(['success' => false, 'message' => 'Rentang tanggal maksimal 31 hari.'], 400);
        }

        // Generate date list (Y-m-d strings)
        $period = CarbonPeriod::create($start->toDateString(), $end->toDateString());
        $dates  = collect($period)->map(fn($d) => $d->toDateString())->toArray();

        // Load all loggers accessible by user
        $loggers = t_Logger::query()
            ->forUser(auth()->user())
            ->orderBy('nama_logger')
            ->get();

        $now = Carbon::now(config('app.timezone'));
        $loggersData = [];

        foreach ($loggers as $logger) {
            $stats = DataMasukStats::forLogger($logger, $dates, $now);

            $days        = [];
            $totalCount  = 0;
            $totalExpect = 0;

            foreach ($dates as $date) {
                $s = $stats[$date];
                $totalCount  += $s['count'];
                $totalExpect += $s['expected'];

                $days[] = [
                    'date'     => $date,
                    'count'    => $s['count'],
                    'expected' => $s['expected'],
                    'pct'      => $s['percentage'],
                ];
            }

            $overallPct = $totalExpect > 0
                ? round(min(100, ($totalCount / $totalExpect) * 100), 2)
                : 0;

            $loggersData[] = [
                'id'             => $logger->id_logger,
                'name'           => $logger->nama_logger ?? $logger->id_logger,
                'table'          => DataMasukStats::resolveTable($logger),
                'days'           => $days,
                'total_count'    => $totalCount,
                'total_expected' => $totalExpect,
                'overall_pct'    => $overallPct,
            ];
        }

        return response()->json([
            'success'    => true,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'dates'      => $dates,
            'loggers'    => $loggersData,
        ]);
    }

    // ---------------------------------------------------------------
    // Detail menit hilang untuk satu logger pada satu tanggal
    // ---------------------------------------------------------------

    public function missingMinutes(Request $request)
    {
        $loggerId = $request->query('logger_id');
        $date     = $request->query('date');

        if (!$loggerId || !$date) {
            return response()->json(['success' => false, 'message' => 'Logger dan tanggal harus diisi.'], 400);
        }

        try {
            $parsed = Carbon::createFromFormat('Y-m-d', $date, config('app.timezone'))->startOfDay();
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Format tanggal tidak valid.'], 400);
        }

        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $loggerId)
            ->first();

        if (!$logger) {
            return response()->json([
                'success' => false,
                'message' => 'Logger tidak ditemukan atau tidak memiliki akses.',
            ], 404);
        }

        $stats = DataMasukStats::missingMinutesForDate($logger, $parsed->toDateString(), Carbon::now(config('app.timezone')));

        return response()->json([
            'success'       => true,
            'logger_id'     => $logger->id_logger,
            'logger_name'   => $logger->nama_logger ?? $logger->id_logger,
            'date'          => $parsed->toDateString(),
            'expected'      => $stats['expected'],
            'present'       => $stats['present'],
            'total_missing' => $stats['missing'],
            'ranges'        => $stats['ranges'],
        ]);
    }

    // ---------------------------------------------------------------
    // Upload CSV untuk melengkapi data yang hilang
    // ---------------------------------------------------------------

    public function uploadCsv(Request $request)
    {
        $request->validate([
            'logger_id' => 'required|string',
            'csv_file'  => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $loggerId = $request->input('logger_id');

        // Pastikan logger ada & user punya akses
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $loggerId)
            ->first();

        if (!$logger) {
            return response()->json([
                'success' => false,
                'message' => 'Logger tidak ditemukan atau tidak memiliki akses.',
            ], 404);
        }

        // Resolusi tabel target
        $tableMain = DataMasukStats::resolveTable($logger);
        $maxSensor = SensorFamily::maxSensorFor($tableMain);

        // Parse CSV
        $file   = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return response()->json(['success' => false, 'message' => 'Gagal membuka file CSV.'], 500);
        }

        // Baca header
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return response()->json(['success' => false, 'message' => 'File CSV kosong atau tidak valid.'], 422);
        }

        // Normalisasi header: lowercase + trim
        $headers = array_map(fn($h) => mb_strtolower(trim($h)), $headers);

        // Kolom meta yang dikecualikan
        $metaCols = ['id_alat', 'tanggal', 'jam', 'waktu', 'id_logger'];

        // Temukan indeks kolom kunci
        $idxIdAlat  = array_search('id_alat',  $headers);
        $idxTanggal = array_search('tanggal',  $headers);
        $idxJam     = array_search('jam',      $headers);
        // Fallback: 'waktu' sebagai single datetime column
        $idxWaktu   = array_search('waktu',    $headers);

        $hasSeparate = ($idxTanggal !== false && $idxJam !== false);
        $hasCombined = ($idxWaktu   !== false);

        if (!$hasSeparate && !$hasCombined) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'CSV harus memiliki kolom "tanggal" + "jam", atau kolom "waktu".',
            ], 422);
        }

        // Buat mapping: colIdx → sensorN  (berdasarkan urutan kolom non-meta)
        // Kolom ke-1 non-meta = sensor1, ke-2 = sensor2, dst.
        $sensorMap = []; // colIdx => 'sensor1', 'sensor2', ...
        $sensorSeq = 0;
        foreach ($headers as $colIdx => $colName) {
            if (in_array($colName, $metaCols, true)) {
                continue;
            }
            $sensorSeq++;
            if ($sensorSeq <= $maxSensor) {
                $sensorMap[$colIdx] = 'sensor' . $sensorSeq;
            }
        }

        // Ambil semua waktu yang sudah ada untuk logger ini (cukup set string)
        // Ini dipakai untuk cek duplikat secara in-memory agar tidak N+1 query
        $existingWaktu = DB::table($tableMain)
            ->where('id_logger', $loggerId)
            ->pluck('waktu')
            ->map(fn($w) => Carbon::parse($w)->format('Y-m-d H:i:s'))
            ->flip()  // key = waktu string, value = index → O(1) lookup
            ->toArray();

        $tz       = config('app.timezone', 'Asia/Jakarta');
        $inserted = 0;
        $skipped  = 0;
        $errors   = [];
        $batch    = [];
        $lineNo   = 1; // header sudah dibaca

        while (($row = fgetcsv($handle)) !== false) {
            $lineNo++;

            // Skip baris kosong
            if (count(array_filter($row, fn($v) => trim($v) !== '')) === 0) {
                continue;
            }

            // Resolusi id_alat dari CSV atau gunakan logger_id dari request
            $csvLoggerId = ($idxIdAlat !== false && isset($row[$idxIdAlat]))
                ? trim($row[$idxIdAlat])
                : $loggerId;

            // Skip baris dengan id_alat berbeda (jika CSV multi-logger)
            if ($csvLoggerId !== $loggerId) {
                $skipped++;
                continue;
            }

            // Resolusi waktu
            try {
                if ($hasSeparate) {
                    $tanggalStr = trim($row[$idxTanggal] ?? '');
                    $jamStr     = trim($row[$idxJam]     ?? '00:00:00');
                    $waktuParsed = Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $tanggalStr . ' ' . $jamStr,
                        $tz
                    )->format('Y-m-d H:i:s');
                } else {
                    $waktuParsed = Carbon::parse(
                        trim($row[$idxWaktu]),
                        $tz
                    )->format('Y-m-d H:i:s');
                }
            } catch (\Throwable $e) {
                $errors[] = "Baris {$lineNo}: format waktu tidak valid — " . ($e->getMessage());
                continue;
            }

            // Skip jika waktu ini sudah ada di database
            if (array_key_exists($waktuParsed, $existingWaktu)) {
                $skipped++;
                continue;
            }

            // Bangun baris data sensor secara positional
            $dataRow = ['id_logger' => $loggerId, 'waktu' => $waktuParsed];

            foreach ($sensorMap as $colIdx => $sensorCol) {
                $val = trim($row[$colIdx] ?? '');
                $dataRow[$sensorCol] = ($val !== '' && is_numeric($val)) ? (float) $val : null;
            }

            // Isi null untuk sensor yang tidak ada di CSV
            for ($i = 1; $i <= $maxSensor; $i++) {
                $dataRow['sensor' . $i] = $dataRow['sensor' . $i] ?? null;
            }

            $batch[] = $dataRow;

            // Tandai waktu ini sebagai sudah ada agar duplikat dalam batch yang sama juga diskip
            $existingWaktu[$waktuParsed] = true;

            // Flush batch setiap 500 baris
            if (count($batch) >= 500) {
                DB::table($tableMain)->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        fclose($handle);

        // Flush sisa batch
        if (!empty($batch)) {
            DB::table($tableMain)->insert($batch);
            $inserted += count($batch);
        }

        return response()->json([
            'success'  => true,
            'inserted' => $inserted,
            'skipped'  => $skipped,
            'errors'   => $errors,
            'message'  => "{$inserted} baris ditambahkan, {$skipped} baris dilewati.",
        ]);
    }

}
