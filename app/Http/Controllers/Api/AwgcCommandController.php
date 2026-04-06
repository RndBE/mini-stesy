<?php

namespace App\Http\Controllers\Api;

use App\Events\SensorDataUpdated;
use App\Http\Controllers\Controller;
use App\Models\AwgcCommandLog;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            'target_bukaan_persen' => 'required|integer|min:0|max:100',
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
        $activeCommand = AwgcCommandLog::where('id_logger', $request->id_logger)
            ->whereIn('status_command', ['pending', 'sent'])
            ->latest()
            ->first();

        if ($activeCommand) {
            return response()->json([
                'success'    => false,
                'message'    => 'Masih ada perintah yang sedang diproses. Tunggu konfirmasi terlebih dahulu.',
                'command_id' => $activeCommand->id,
                'status'     => $activeCommand->status_command,
            ], 409); // Conflict
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

        // Publish ke MQTT
        $mqttSuccess = $this->publishToMqtt($logger->id_logger, [
            'command'         => 'set_gate',
            'target_persen'   => $request->target_bukaan_persen,
            'target_cm'       => $targetCm,
            'command_id'      => $commandLog->id,
            'timestamp'       => now()->toIso8601String(),
        ]);

        if ($mqttSuccess) {
            $commandLog->update([
                'status_command' => 'sent',
                'sent_at'        => now(),
            ]);
        } else {
            $commandLog->update([
                'status_command' => 'error',
                'pesan_error'    => 'Gagal terhubung ke MQTT broker',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Perintah tersimpan namun gagal dikirim ke alat. Cek koneksi MQTT.',
                'command_id' => $commandLog->id,
            ], 500);
        }

        // Broadcast ke semua browser yg membuka skema: status = 'command_sent'
        try {
            broadcast(new SensorDataUpdated([
                'node_id'         => $request->node_skema_id,
                'id_logger'       => $request->id_logger,
                'jenis_alat'      => 'AWGC',
                'event_type'      => 'command_sent',
                'command_id'      => $commandLog->id,
                'target_persen'   => $request->target_bukaan_persen,
                'target_cm'       => $targetCm,
                'status_command'  => 'sent',
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
     * Publish perintah ke MQTT broker menggunakan library phpMQTT.
     */
    private function publishToMqtt(string $idLogger, array $payload): bool
    {
        try {
            $mqtt = new \phpMQTT(
                env('MQTT_HOST', '72.60.78.159'),
                (int) env('MQTT_PORT', 1883),
                'laravel-awgc-' . uniqid()
            );

            if (!$mqtt->connect(true, null, env('MQTT_USER'), env('MQTT_PASS'))) {
                Log::error('MQTT connect gagal saat kirim perintah AWGC');
                return false;
            }

            $topic   = "stesy/awgc/{$idLogger}/command";
            $message = json_encode($payload);

            $mqtt->publish($topic, $message, 1); // QoS 1 = at least once
            $mqtt->close();

            Log::info("AWGC command published to {$topic}: {$message}");
            return true;
        } catch (\Exception $e) {
            Log::error('MQTT publish error: ' . $e->getMessage());
            return false;
        }
    }
}
