<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Models\Parameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataPerangkatApiController extends Controller
{
    /**
     * GET /api/v1/mobile/data-perangkat
     * List semua logger (data perangkat) milik user.
     */
    public function index(Request $request)
    {
        $query = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'params', 'jiat', 'nonjiat', 'temp16', 'temp19'])
            ->orderBy('id_logger');

        // Filter by kategori name (e.g. ?kategori=AWLR) or id (e.g. ?id_katlogger=2)
        if ($request->filled('kategori')) {
            $query->whereHas('kategori', function ($q) use ($request) {
                $q->where('nama_kategori', 'like', '%' . $request->kategori . '%')
                  ->orWhere('kode', strtoupper($request->kategori));
            });
        }
        if ($request->filled('id_katlogger')) {
            $query->where('id_katlogger', $request->id_katlogger);
        }

        $devices = $query->get()->map(fn($d) => $this->formatDevice($d));

        return response()->json([
            'success' => true,
            'data'    => $devices,
            'meta'    => [
                'total'    => $devices->count(),
                'kategori' => $request->get('kategori'),
            ],
        ]);
    }

    /**
     * GET /api/v1/mobile/data-perangkat/{id}
     * Detail satu logger + params.
     */
    public function show(Request $request, string $id)
    {
        $device = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'kategori', 'params', 'jiat', 'nonjiat'])
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatDevice($device, detail: true),
        ]);
    }

    /**
     * POST /api/v1/mobile/data-perangkat
     * Tambah entri data perangkat baru (t_Logger).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_logger'      => 'required|string|max:255',
            'id_katlogger'     => 'required|integer',
            'tabel_main'       => 'nullable|string',
            'sensor_count'     => 'nullable|integer|in:16,19',
        ]);

        $device = t_Logger::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil ditambahkan',
            'data'    => ['id_logger' => $device->id_logger],
        ], 201);
    }

    /**
     * PUT /api/v1/mobile/data-perangkat/{id}
     * Update data perangkat.
     */
    public function update(Request $request, string $id)
    {
        $device = t_Logger::query()
            ->forUser($request->user())
            ->where('id_logger', $id)
            ->first();

        if (!$device) {
            return response()->json(['success' => false, 'message' => 'Device tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'nama_logger'  => 'sometimes|string|max:255',
            'id_katlogger' => 'sometimes|integer',
            'tabel_main'   => 'sometimes|string',
        ]);

        $device->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Device berhasil diupdate',
        ]);
    }

    private function formatDevice(t_Logger $d, bool $detail = false): array
    {
        $waktu16  = optional($d->temp16)->waktu;
        $waktu19  = optional($d->temp19)->waktu;
        $lastTime = collect([$waktu16, $waktu19])->filter()->sortDesc()->first();
        $diffMin  = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
        $status   = ($diffMin !== null && $diffMin < 60) ? 'online' : 'offline';

        $base = [
            'id_logger'    => $d->id_logger,
            'nama_logger'  => $d->nama_logger,
            'id_katlogger' => $d->id_katlogger,
            'kategori'     => $d->kategori?->nama_kategori,
            'tabel_main'   => $d->tabel_main,
            'sensor_count' => (int) ($d->sensor_count ?? (str_contains((string) $d->tabel_main, '19') ? 19 : 16)),
            'status'       => $status,
            'last_time'    => $lastTime,
            'lokasi'       => [
                'nama_lokasi' => $d->lokasi?->nama_lokasi,
                'alamat'      => $d->lokasi?->alamat,
                'latitude'    => $d->lokasi?->latitude ? (float) $d->lokasi->latitude : null,
                'longitude'   => $d->lokasi?->longitude ? (float) $d->lokasi->longitude : null,
            ],
            'params' => $d->params->map(fn($p) => [
                'id_param'           => $p->id_param,
                'nama_parameter'     => $p->nama_parameter,
                'kolom_sensor'       => $p->kolom_sensor,
                'satuan'             => $p->satuan,
                'parameter_group_id' => $p->parameter_group_id,
            ])->values(),
        ];

        if ($detail) {
            $base['jiat']    = $d->jiat ? [
                'kedalaman_sumur' => $d->jiat->kedalaman_sumur,
                'kedalaman_pompa' => $d->jiat->kedalaman_pompa,
                'kedalaman_sensor' => $d->jiat->kedalaman_sensor,
            ] : null;
            $base['nonjiat'] = $d->nonjiat ? (array) $d->nonjiat->only(['id', 'jarak_sensor_ke_air', 'tinggi_sensor']) : null;
        }

        return $base;
    }
}
