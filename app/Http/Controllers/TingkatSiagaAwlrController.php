<?php

namespace App\Http\Controllers;

use App\Models\t_Logger;
use App\Models\TingkatSiagaAwlr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TingkatSiagaAwlrController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $loggers = $this->awlrLoggersQuery()
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

        $rows = $this->buildRows($loggers);

        return view('tingkat-siaga-awlr.index', [
            'title' => 'Tingkat Siaga AWLR',
            'rows' => $rows,
            'q' => $q,
        ]);
    }

    public function update(Request $request, string $idLogger)
    {
        $logger = $this->awlrLoggersQuery()
            ->where('id_logger', $idLogger)
            ->firstOrFail();

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

        $freshLogger = $this->awlrLoggersQuery()
            ->where('id_logger', $idLogger)
            ->firstOrFail();

        $row = $this->buildRows(collect([$freshLogger]))->first();

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan notifikasi berhasil disimpan.',
            'row' => $row,
        ]);
    }

    private function awlrLoggersQuery()
    {
        return t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'kategori'])
            ->where(function ($query) {
                $query->where('id_katlogger', 1)
                    ->orWhereHas('kategori', function ($kategoriQ) {
                        $kategoriQ->whereRaw('LOWER(nama_kategori) = ?', ['awlr']);
                    });
            });
    }

    private function buildRows($loggers)
    {
        $levelsByLogger = TingkatSiagaAwlr::query()
            ->whereIn('id_logger', $loggers->pluck('id_logger'))
            ->orderBy('id_status')
            ->orderBy('nilai')
            ->get()
            ->groupBy('id_logger');

        return $loggers->map(function ($logger) use ($levelsByLogger) {
            $levels = ($levelsByLogger->get($logger->id_logger) ?? collect())
                ->where('status', 1)
                ->values();

            $isNotifActive = $levels->isNotEmpty();
            $namaLokasi = optional($logger->lokasi)->nama_lokasi;
            $namaPos = $logger->nama_logger ?: $namaLokasi;

            return [
                'id_logger' => $logger->id_logger,
                'nama_pos' => $namaPos,
                'nama_lokasi' => $namaLokasi,
                'status_notifikasi' => $isNotifActive ? 'Aktif' : 'Tidak Aktif',
                'status_notifikasi_bool' => $isNotifActive,
                'jeda_notif' => $isNotifActive && $logger->jeda_notif !== null
                    ? (int) $logger->jeda_notif . ' menit'
                    : '-',
                'jeda_notif_value' => $logger->jeda_notif !== null ? (int) $logger->jeda_notif : 1,
                'levels' => $levels->map(function ($level) {
                    return [
                        'nama' => $level->nama,
                        'nilai' => rtrim(rtrim(number_format((float) $level->nilai, 2, '.', ''), '0'), '.'),
                        'warna' => (string) $level->warna,
                    ];
                })->all(),
            ];
        })->values();
    }
}
