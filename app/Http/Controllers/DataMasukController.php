<?php

namespace App\Http\Controllers;

use App\Models\T_s16;
use App\Models\T_s19;
use App\Models\t_Logger;
use App\Models\Parameter_sensor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DataMasukController extends Controller
{
    public function index()
    {
        $loggers = t_Logger::all();
        return view('data-masuk.index',['title' => 'Data Masuk'], compact('loggers'));
    }

    public function getData(Request $request)
    {
        try {
            $logger_id = $request->query('logger_id');
            $tanggal = $request->query('tanggal');

            if (!$logger_id || !$tanggal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Logger ID dan tanggal harus diisi.'
                ], 400);
            }

            try {
                $tanggalParsed = Carbon::createFromFormat('Y-m-d', $tanggal, config('app.timezone'))->startOfDay();
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format tanggal tidak valid.'
                ], 400);
            }

            $now = Carbon::now(config('app.timezone'));
            $tanggalAwal = $tanggalParsed->copy()->startOfDay();

            if ($tanggalParsed->isSameDay($now)) {
                $tanggalAkhir = $now;
            } else {
                $tanggalAkhir = $tanggalParsed->copy()->endOfDay();
            }

            $parameters = Parameter_sensor::where('logger_id', $logger_id)->get();

            $sensorCount = null;
            $loggerRow = t_Logger::where('id_logger', $logger_id)->first();
            if ($loggerRow && isset($loggerRow->jumlah_sensor) && is_numeric($loggerRow->jumlah_sensor)) {
                $sensorCount = (int) $loggerRow->jumlah_sensor;
            }

            if (!$sensorCount) {
                $paramCount = $parameters->count();
                if ($paramCount >= 19) $sensorCount = 19;
                elseif ($paramCount >= 16) $sensorCount = 16;
                else $sensorCount = 16;
            }

            $useS19 = false;

            if ($sensorCount >= 19) {
                $useS19 = true;
            } else {
                $hasS19 = T_s19::where('id_logger', $logger_id)
                    ->whereBetween('waktu', [$tanggalAwal, $tanggalAkhir])
                    ->exists();
                if ($hasS19) $useS19 = true;
            }

            if ($useS19) {
                $query = T_s19::where('id_logger', $logger_id)
                    ->whereBetween('waktu', [$tanggalAwal, $tanggalAkhir])
                    ->orderBy('waktu', 'desc');
                $sensorCount = 19;
            } else {
                $query = T_s16::where('id_logger', $logger_id)
                    ->whereBetween('waktu', [$tanggalAwal, $tanggalAkhir])
                    ->orderBy('waktu', 'desc');
                $sensorCount = 16;
            }

            $columns = [];
            $parameterMap = [];

            for ($i = 1; $i <= $sensorCount; $i++) {
                $sensorKey = 'sensor' . $i;
                $param = $parameters->firstWhere('kolom_sensor', $sensorKey);

                $columnHeader = $param && $param->nama_parameter
                    ? $param->nama_parameter
                    : 'Sensor ' . $i;

                $columns[] = $columnHeader;
                $parameterMap[$columnHeader] = $sensorKey;
            }

            $rawData = $query->get();

            $data = $rawData->map(function ($row) use ($columns, $parameterMap) {
                $transformed = [
                    'waktu' => $row->waktu
                ];
                foreach ($columns as $col) {
                    $key = $parameterMap[$col] ?? null;
                    $transformed[strtolower($col)] = $key ? ($row->{$key} ?? null) : null;
                }
                return $transformed;
            })->toArray();

            // Calculate completeness: actual records / expected records per day (1440 minutes)
            $expectedRecords = 1440; // 24 hours * 60 minutes per day
            $actualRecords = count($data);
            $completeness = round(($actualRecords / $expectedRecords) * 100, 2);

            return response()->json([
                'success' => true,
                'data' => $data,
                'columns' => $columns,
                'completeness' => $completeness,
                'total' => count($data),
                'range' => [
                    'start' => $tanggalAwal->toDateTimeString(),
                    'end' => $tanggalAkhir->toDateTimeString(),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('DataMasukController@getData Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
