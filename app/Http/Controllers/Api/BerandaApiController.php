<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Models\Instansi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BerandaApiController extends Controller
{
    /**
     * GET /api/v1/mobile/beranda/info
     * Info utama untuk halaman beranda:
     * - Info instansi (nama, alamat, logo)
     * - Info user login
     * - Status live monitoring (jumlah online/total)
     * - Tanggal hari ini
     */
    public function info(Request $request)
    {
        $user = $request->user();

        // Ambil instansi user
        $instansi = null;
        if ($user->instansi_id) {
            $instansi = Instansi::find($user->instansi_id);
        }
        $judulMobile = $instansi ? trim((string) $instansi->judul_mobile) : '';
        $headerTitle = $judulMobile !== ''
            ? $judulMobile
            : ($instansi ? 'Telemetri ' . $instansi->nama : 'Telemetri');

        // Hitung logger yang online/offline milik user
        $loggers = t_Logger::query()
            ->forUser($user)
            ->with(['temp16', 'temp19', 'temp50'])
            ->get();

        $totalLogger  = $loggers->count();
        $onlineCount  = 0;
        $offlineCount = 0;

        foreach ($loggers as $lg) {
            $waktu16  = optional($lg->temp16)->waktu;
            $waktu19  = optional($lg->temp19)->waktu;
            $waktu50  = optional($lg->temp50)->waktu;
            $lastTime = collect([$waktu16, $waktu19, $waktu50])->filter()->sortDesc()->first();
            $diffMin  = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
            $isOnline = $diffMin !== null && $diffMin < 60;

            $isOnline ? $onlineCount++ : $offlineCount++;
        }

        // Daftar kategori logger yang dimiliki user (untuk menu)
        $kategoriList = t_Logger::query()
            ->forUser($user)
            ->with('kategori')
            ->get()
            ->pluck('kategori')
            ->filter()
            ->unique('id_katlogger')
            ->map(fn($k) => [
                'id_katlogger'  => $k->id_katlogger,
                'nama_kategori' => $k->nama_kategori,
                'kode'          => $k->kode ?? null,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id_user'    => $user->id_user,
                    'nama'       => $user->nama,
                    'username'   => $user->username,
                    'level_user' => $user->level_user,
                ],
                'instansi' => $instansi ? [
                    'nama'         => $instansi->nama,
                    'judul_mobile' => $instansi->judul_mobile,
                    'alamat'       => $instansi->alamat,
                    'telp'         => $instansi->telp,
                    'logo'         => $instansi->logo ? asset('storage/' . $instansi->logo) : null,
                    'logo_mobile'  => $instansi->logo_mobile ? asset('storage/' . $instansi->logo_mobile) : null,
                ] : null,
                'header_title'  => $headerTitle,
                'monitoring' => [
                    'is_live'       => $onlineCount > 0,
                    'online_count'  => $onlineCount,
                    'offline_count' => $offlineCount,
                    'total_count'   => $totalLogger,
                ],
                'kategori_list' => $kategoriList,
                'tanggal'       => now()->translatedFormat('l, d F Y'),
                'generated_at'  => now()->toIso8601String(),
            ],
        ]);
    }
}
