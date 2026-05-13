<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalisaApiController extends Controller
{
    /**
     * GET /api/v1/mobile/analisa/{id_logger}
     * Info logger + params yang tersedia untuk analisa.
     */
    public function index(Request $request, string $id)
    {
        $device = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'params'])
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Logger tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id_logger'   => $device->id_logger,
                'nama_logger' => $device->nama_logger,
                'kategori'    => $device->kategori?->nama_kategori,
                'nama_lokasi' => $device->lokasi?->nama_lokasi,
                'tabel_main'  => $device->tabel_main,
                'params'      => $device->params->map(fn($p) => [
                    'id_param'       => $p->id_param,
                    'nama_parameter' => $p->nama_parameter,
                    'kolom_sensor'   => $p->kolom_sensor,
                    'satuan'         => $p->satuan,
                    'tipe_graf'      => $p->tipe_graf ?? 'line',
                ])->values(),
            ],
        ]);
    }

    /**
     * GET /api/v1/mobile/analisa/{id_logger}/data
     * Data historis sensor dengan filter tanggal dan parameter.
     *
     * Query params:
     *   - from        : tanggal awal  (Y-m-d), default: 7 hari lalu
     *   - to          : tanggal akhir (Y-m-d), default: hari ini
     *   - parameter   : nama_parameter atau kolom_sensor untuk filter kolom
     *   - per_page    : jumlah data per halaman (default 200, max 1000)
     *   - page        : nomor halaman (default 1)
     */
    public function data(Request $request, string $id)
    {
        $device = t_Logger::query()
            ->forUser($request->user())
            ->with(['params'])
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Logger tidak ditemukan'], 404);
        }

        $from    = $request->get('from', now()->subDays(7)->format('Y-m-d'));
        $to      = $request->get('to', now()->format('Y-m-d'));
        $perPage = min((int) $request->get('per_page', 200), 1000);
        $page    = max((int) $request->get('page', 1), 1);
        $paramFilter = $request->get('parameter');

        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt   = Carbon::parse($to)->endOfDay();

        // Tentukan tabel yang dipakai
        $tableMain = trim((string) ($device->tabel_main ?? ''));
        if (!$tableMain || !preg_match('/^t_s(16|19)_\d{2,}$/', $tableMain)) {
            $tableMain = 't_s16_01';
        }

        // Filter kolom sensor jika ada parameter
        $sensorColumns = [];
        if ($paramFilter) {
            $matchedParam = $device->params->first(function ($p) use ($paramFilter) {
                return strtolower($p->nama_parameter ?? '') === strtolower($paramFilter)
                    || strtolower($p->kolom_sensor ?? '') === strtolower($paramFilter);
            });
            if ($matchedParam) {
                $sensorColumns = [$matchedParam->kolom_sensor];
            }
        }

        // Ambil semua kolom sensor yang ada di params
        if (empty($sensorColumns)) {
            $sensorColumns = $device->params->pluck('kolom_sensor')->filter()->unique()->values()->toArray();
        }

        try {
            $query = DB::table($tableMain)
                ->where('id_logger', $id)
                ->whereBetween('waktu', [$fromDt, $toDt])
                ->orderBy('waktu', 'desc');

            $total  = $query->count();
            $offset = ($page - 1) * $perPage;

            $rawData = $query->offset($offset)->limit($perPage)->get();

            // Format: hanya tampilkan kolom sensor yang relevan
            $data = $rawData->map(function ($row) use ($sensorColumns) {
                $result = ['waktu' => $row->waktu ?? null];
                foreach ($sensorColumns as $col) {
                    $result[$col] = $row->{$col} ?? null;
                }
                return $result;
            });

            return response()->json([
                'success' => true,
                'data'    => $data,
                'params'  => $device->params->map(fn($p) => [
                    'nama_parameter' => $p->nama_parameter,
                    'kolom_sensor'   => $p->kolom_sensor,
                    'satuan'         => $p->satuan,
                    'tipe_graf'      => $p->tipe_graf ?? 'line',
                ])->values(),
                'meta'    => [
                    'id_logger' => $id,
                    'tabel'     => $tableMain,
                    'from'      => $fromDt->toIso8601String(),
                    'to'        => $toDt->toIso8601String(),
                    'total'     => $total,
                    'per_page'  => $perPage,
                    'page'      => $page,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error ambil data: ' . $e->getMessage(),
            ], 500);
        }
    }
}
