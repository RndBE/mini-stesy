<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\t_Logger;

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
            // Resolve main table (same pattern as DataMasukController)
            $sensorCount = $this->resolveSensorCount($logger);
            $tableMain   = $this->resolveMainTable($logger->tabel_main ?? null, $sensorCount);

            // Get counts grouped by date in one query.
            // COUNT DISTINCT per-minute to deduplicate double-sends within the same minute.
            $counts = DB::table($tableMain)
                ->selectRaw("DATE(waktu) as tgl, COUNT(DISTINCT DATE_FORMAT(waktu, '%Y-%m-%d %H:%i')) as cnt")
                ->where('id_logger', $logger->id_logger)
                ->whereBetween('waktu', [$start, $end])
                ->groupByRaw('DATE(waktu)')
                ->pluck('cnt', 'tgl');

            // Build per-day breakdown
            $days        = [];
            $totalCount  = 0;
            $totalExpect = 0;

            foreach ($dates as $date) {
                $dayCarbon = Carbon::parse($date, config('app.timezone'));
                $count     = (int) ($counts[$date] ?? 0);

                // Future days: tidak dihitung (tampil "–")
                if ($dayCarbon->startOfDay()->gt($now->copy()->startOfDay())) {
                    $expected = 0;
                    $pct      = 0;
                } elseif ($dayCarbon->isSameDay($now)) {
                    // Hari ini: expected = menit yang sudah berlalu sejak tengah malam
                    $expected = max(1, (int) $now->copy()->startOfDay()->diffInMinutes($now));
                    $pct      = (int) round(min(100, ($count / $expected) * 100));
                } else {
                    // Hari lampau: expected = 1440 menit (1 hari penuh)
                    $expected = 1440;
                    $pct      = (int) round(min(100, ($count / $expected) * 100));
                }

                $totalCount  += $count;
                $totalExpect += $expected;

                $days[] = [
                    'date'     => $date,
                    'count'    => $count,
                    'expected' => $expected,
                    'pct'      => $pct,
                ];
            }

            $overallPct = $totalExpect > 0
                ? (int) round(min(100, ($totalCount / $totalExpect) * 100))
                : 0;

            $loggersData[] = [
                'id'          => $logger->id_logger,
                'name'        => $logger->nama_logger ?? $logger->id_logger,
                'table'       => $tableMain,
                'days'        => $days,
                'total_count' => $totalCount,
                'total_expected' => $totalExpect,
                'overall_pct' => $overallPct,
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
    // Helpers (mirrors DataMasukController)
    // ---------------------------------------------------------------

    /**
     * Detect the logger's sending interval in minutes by inspecting
     * the time gap between its most recent consecutive records.
     * Falls back to 1 minute if data is insufficient.
     */
    private function detectIntervalMinutes(string $tableMain, string $loggerId): int
    {
        try {
            $times = DB::table($tableMain)
                ->where('id_logger', $loggerId)
                ->orderBy('waktu', 'desc')
                ->limit(10)
                ->pluck('waktu');

            if ($times->count() < 2) return 1;

            $intervals = [];
            for ($i = 0; $i < min($times->count() - 1, 5); $i++) {
                $diff = (int) abs(
                    Carbon::parse($times[$i])->diffInSeconds(Carbon::parse($times[$i + 1]))
                );
                if ($diff > 0) $intervals[] = $diff;
            }

            if (empty($intervals)) return 1;

            // Use median to avoid outliers, convert seconds → minutes
            sort($intervals);
            $median = $intervals[(int) floor(count($intervals) / 2)];
            return max(1, (int) round($median / 60));
        } catch (\Throwable $e) {
            return 1;
        }
    }

    private function resolveSensorCount($logger): int
    {
        if (isset($logger->jumlah_sensor) && is_numeric($logger->jumlah_sensor)) {
            return (int) $logger->jumlah_sensor;
        }
        return 16;
    }

    private function resolveMainTable(?string $tableMain, int $sensorCount): string
    {
        $tableMain = trim((string) $tableMain);
        if ($this->isSupportedTable($tableMain)) {
            return $tableMain;
        }
        return $sensorCount >= 19 ? 't_s19_01' : 't_s16_01';
    }

    private function isSupportedTable(string $tableName): bool
    {
        if (!preg_match('/^t_s(16|19)_\d{2,}$/', $tableName)) {
            return false;
        }
        return Schema::hasTable($tableName);
    }
}
