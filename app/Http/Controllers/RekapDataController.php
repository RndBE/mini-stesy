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

            // Get counts grouped by date in one query
            $counts = DB::table($tableMain)
                ->selectRaw('DATE(waktu) as tgl, COUNT(*) as cnt')
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
                // Future days not counted
                if ($dayCarbon->startOfDay()->gt($now->copy()->startOfDay())) {
                    $expected = 0;
                } else {
                    $expected = 1440;
                }

                $count       = (int) ($counts[$date] ?? 0);
                $pct         = $expected > 0 ? round(min(100, ($count / $expected) * 100), 1) : 0;
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
                ? round(min(100, ($totalCount / $totalExpect) * 100), 1)
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
