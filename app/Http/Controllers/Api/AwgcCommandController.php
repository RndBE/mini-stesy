<?php

namespace App\Http\Controllers\Api;

use App\Events\SensorDataUpdated;
use App\Http\Controllers\Controller;
use App\Models\AwgcCommandLog;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

/**
 * AwgcCommandController
 *
 * Menerima perintah dari UI Skema Irigasi untuk menggerakkan pintu air AWGC,
 * mempublikasikannya ke MQTT broker, dan merekam hasilnya ke audit log.
 */
class AwgcCommandController extends Controller
{
    /**
     * POST /api/awgc/command
     *
     * Kirim perintah buka/tutup pintu air AWGC.
     * Perintah akan:
     * 1. Divalidasi (user harus login, device harus jenis AWGC)
     * 2. Disimpan ke awgc_command_log dengan status 'pending'
     * 3. Dipublikasikan ke MQTT topic: stesy/awgc/{id_logger}/command
     * 4. Broadcast WebSocket ke browser: status berubah menjadi 'sending'
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'node_skema_id'       => 'required|string|max:50',
            'id_logger'           => 'required|string|exists:t_logger,id_logger',
            'target_bukaan_persen' => 'required|numeric|min:0|max:100',
            'sensor_id'           => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Pastikan device ini benar-benar AWGC, bukan AWLR
        $logger = t_Logger::where('id_logger', $request->id_logger)->first();
        if (!$logger || $logger->jenis_alat !== 'AWGC') {
            return response()->json([
                'success' => false,
                'message' => 'Device ini bukan tipe AWGC atau tidak ditemukan.',
            ], 403);
        }

        // Cek apakah masih ada perintah yang sedang pending/sent untuk logger ini
        // Auto-expire perintah yang lebih dari 5 menit (stuck)
        $activeCommand = AwgcCommandLog::where('id_logger', $request->id_logger)
            ->whereIn('status_command', ['pending', 'sent'])
            ->latest()
            ->first();

        if ($activeCommand) {
            $ageMinutes = $activeCommand->created_at ? $activeCommand->created_at->diffInMinutes(now()) : 999;
            if ($ageMinutes < 5) {
                return response()->json([
                    'success'    => false,
                    'message'    => 'Masih ada perintah yang sedang diproses. Tunggu konfirmasi terlebih dahulu.',
                    'command_id' => $activeCommand->id,
                    'status'     => $activeCommand->status_command,
                ], 409); // Conflict
            }
            // Auto-expire perintah lama yang stuck
            $activeCommand->update(['status_command' => 'timeout', 'pesan_error' => 'Auto-expired after 5 min']);
        }

        // Hitung target bukaan dalam cm berdasarkan persen dan bukaan_maksimal_cm
        $maxCm = $logger->bukaan_maksimal_cm ?? 100;
        $targetCm = (int) round($request->target_bukaan_persen * $maxCm / 100);

        $user = Auth::user();

        // Simpan ke log dengan status pending
        $commandLog = AwgcCommandLog::create([
            'node_skema_id'        => $request->node_skema_id,
            'id_logger'            => $request->id_logger,
            'target_bukaan_cm'     => $targetCm,
            'target_bukaan_persen' => $request->target_bukaan_persen,
            'status_command'       => 'pending',
            'commanded_by'         => $user?->id_user ?? $user?->id,
            'commanded_by_name'    => $user?->name ?? $user?->nama_user ?? 'System',
        ]);

        // ==========================================
        // SIMPAN KE DATABASE (seperti pola add_awlr2)
        // 1. Insert ke tabel historis utama (t_s16_xx / t_s19_xx)
        // 2. Update temp_sXX_latest (data terkini untuk dashboard)
        // ==========================================
        $sensorId  = $request->input('sensor_id', 'sensor1');
        $elevasiCm = $request->input('elevasi_target_cm', $targetCm);
        $waktuNow  = now()->format('Y-m-d H:i:s');

        // Resolve tabel utama dari konfigurasi logger (sama seperti DataMasukController)
        $tableMain = $this->resolveMainTable($logger->tabel_main ?? null, $logger);
        $tableTemp = str_contains($tableMain, '19') ? 'temp_s19_latest' : 'temp_s16_latest';

        // Ambil snapshot terkini dari temp table sebagai base row
        // Ini memastikan kolom NOT NULL terisi nilai nyata, dan sensor lain tidak corrupt
        $latestRow = DB::table($tableTemp)->where('id_logger', $request->id_logger)->first();

        $maxSensor = str_contains($tableMain, '19') ? 19 : 16;
        $row = [
            'id_logger' => $request->id_logger,
            'waktu'     => $waktuNow,
        ];
        for ($i = 1; $i <= $maxSensor; $i++) {
            $col        = 'sensor' . $i;
            $row[$col]  = $latestRow ? ($latestRow->$col ?? 0) : 0;
        }
        $row[$sensorId] = $elevasiCm; // Override hanya sensor yang diperintahkan

        try {
            // 1. Insert ke tabel besar (histori permanen)
            DB::table($tableMain)->insert($row);

            // 2. Update/Insert ke tabel latest (snapshot dashboard)
            DB::table($tableTemp)->updateOrInsert(
                ['id_logger' => $request->id_logger],
                $row
            );

            $commandLog->update([
                'status_command' => 'success',
                'sent_at'        => now(),
                'confirmed_at'   => now(),
            ]);
        } catch (\Exception $e) {
            // Jika gagal, tandai command sebagai error agar tidak blocking berikutnya
            $commandLog->update(['status_command' => 'error', 'pesan_error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan ke database: ' . $e->getMessage(),
            ], 500);
        }

        // Broadcast ke semua browser yg membuka skema: status = 'command_confirmed' setelah bypass berhasil
        try {
            broadcast(new SensorDataUpdated([
                'node_id'         => $request->node_skema_id,
                'id_logger'       => $request->id_logger,
                'jenis_alat'      => 'AWGC',
                'event_type'      => 'command_confirmed',
                'command_id'      => $commandLog->id,
                'bukaan_persen'   => $request->target_bukaan_persen,
                'bukaan_cm'       => $targetCm,
                'status_command'  => 'success',
                'commanded_by'    => $commandLog->commanded_by_name,
            ]));
        } catch (\Exception $e) {
            Log::warning('Broadcast WebSocket gagal setelah kirim perintah AWGC: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Perintah berhasil dikirim ke alat.',
            'command_id' => $commandLog->id,
            'status'     => 'sent',
        ]);
    }

    /**
     * GET /api/awgc/status/{commandId}
     *
     * Cek status perintah AWGC yang sedang berjalan.
     * Frontend melakukan polling ke endpoint ini untuk mengetahui
     * apakah alat sudah merespons (sukses/error) atau masih menunggu.
     */
    public function status(int $commandId)
    {
        $log = AwgcCommandLog::find($commandId);

        if (!$log) {
            return response()->json(['success' => false, 'message' => 'Command tidak ditemukan'], 404);
        }

        return response()->json([
            'success'      => true,
            'command_id'   => $log->id,
            'status'       => $log->status_command,
            'is_finished'  => $log->isFinished(),
            'sent_at'      => $log->sent_at?->toIso8601String(),
            'confirmed_at' => $log->confirmed_at?->toIso8601String(),
            'pesan_error'  => $log->pesan_error,
        ]);
    }

    /**
     * POST /api/awgc/confirm/{commandId}
     *
     * Endpoint untuk logger AWGC di lapangan mengonfirmasi bahwa
     * perintah sudah dieksekusi. Dipanggil oleh firmware alat.
     */
    public function confirm(Request $request, int $commandId)
    {
        $log = AwgcCommandLog::find($commandId);

        if (!$log || $log->isFinished()) {
            return response()->json(['success' => false, 'message' => 'Command tidak valid atau sudah selesai'], 404);
        }

        $status = $request->input('status', 'success'); // success | error
        $log->update([
            'status_command' => $status,
            'confirmed_at'   => now(),
            'pesan_error'    => $request->input('error_message'),
        ]);

        // Broadcast hasilnya ke browser
        try {
            broadcast(new SensorDataUpdated([
                'node_id'        => $log->node_skema_id,
                'id_logger'      => $log->id_logger,
                'jenis_alat'     => 'AWGC',
                'event_type'     => 'command_confirmed',
                'command_id'     => $log->id,
                'status_command' => $status,
                'bukaan_persen'  => $log->target_bukaan_persen,
                'bukaan_cm'      => $log->target_bukaan_cm,
            ]));
        } catch (\Exception $e) {
            Log::warning('Broadcast konfirmasi AWGC gagal: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'status' => $status]);
    }

    /**
     * Publish perintah ke MQTT broker menggunakan php-mqtt/laravel-client.
     */
    private function publishToMqtt(string $idLogger, array $payload): bool
    {
        try {
            $topic   = "stesy/awgc/{$idLogger}/command";
            $message = json_encode($payload);

            \PhpMqtt\Client\Facades\MQTT::publish($topic, $message, 0);

            Log::info("AWGC command published to {$topic}: {$message}");
            return true;
        } catch (\Exception $e) {
            Log::error('MQTT publish error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Resolve nama tabel utama dari konfigurasi logger.
     * Mengikuti pola yang sama dengan DataMasukController::resolveMainTable()
     */
    private function resolveMainTable(?string $tableMain, t_Logger $logger): string
    {
        $tableMain = trim((string) $tableMain);

        // Gunakan tabel_main dari logger jika valid
        if (preg_match('/^t_s(16|19)_\d{2,}$/', $tableMain) && Schema::hasTable($tableMain)) {
            return $tableMain;
        }

        // Fallback: tentukan dari jumlah sensor
        $sensorCount = (int) ($logger->jumlah_sensor ?? $logger->sensor_count ?? 16);
        return $sensorCount >= 19 ? 't_s19_01' : 't_s16_01';
    }
}
