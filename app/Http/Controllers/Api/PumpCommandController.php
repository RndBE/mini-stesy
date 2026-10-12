<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PumpControlLog;
use App\Models\t_Logger;
use App\Services\PumpCommandException;
use App\Services\PumpMqttCommandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * PumpCommandController (web)
 *
 * Kontrol pompa AWLR JIAT dari dashboard web via MQTT.
 *
 * Alur:
 *   1. verifyPin  : user memasukkan PIN kontrol instansi (hash di tabel instansi).
 *                   PIN benar -> sesi "terbuka" untuk logger itu selama UNLOCK_TTL detik.
 *   2. command    : get/on/off. on/off hanya diizinkan bila sesi masih terbuka.
 *
 * Protocol MQTT ada di PumpMqttCommandService.
 */
class PumpCommandController extends Controller
{
    /**
     * Lama izin kontrol setelah PIN benar (detik).
     */
    private const UNLOCK_TTL = 300;

    /**
     * POST /api/pump/verify-pin
     *
     * Body:
     *   id_logger   : string (required)
     *   control_pin : string (required)
     */
    public function verifyPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_logger'   => ['required', 'string'],
            'control_pin' => ['required', 'string', 'max:32'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $logger = $this->findPumpLogger($request, $request->input('id_logger'));

        if (!$logger) {
            return $this->noPumpResponse();
        }

        $instansi = $logger->instansi;

        if (!$instansi || !$instansi->has_control_pin) {
            $this->createControlLog(
                $request,
                $logger,
                'unlock',
                'pin_not_configured',
                'PIN kontrol belum dikonfigurasi untuk instansi ini.',
            );

            return response()->json([
                'success' => false,
                'message' => 'PIN kontrol belum dikonfigurasi untuk instansi ini.',
                'step'    => 'pin_not_configured',
            ], 403);
        }

        if (!Hash::check((string) $request->input('control_pin'), (string) $instansi->control_pin_hash)) {
            $this->createControlLog(
                $request,
                $logger,
                'unlock',
                'pin_failed',
                'PIN kontrol tidak sesuai.',
            );

            return response()->json([
                'success' => false,
                'message' => 'PIN kontrol tidak sesuai.',
                'step'    => 'pin_failed',
            ], 403);
        }

        $request->session()->put($this->unlockKey($logger->id_logger), now()->addSeconds(self::UNLOCK_TTL)->timestamp);

        return response()->json([
            'success'    => true,
            'message'    => 'PIN benar.',
            'expires_in' => self::UNLOCK_TTL,
        ]);
    }

    /**
     * POST /api/pump/command
     *
     * Body:
     *   id_logger : string (required)
     *   action    : "get" | "on" | "off" (required)
     */
    public function command(Request $request, PumpMqttCommandService $pumpService)
    {
        $validator = Validator::make($request->all(), [
            'id_logger' => ['required', 'string'],
            'action'    => ['required', 'string', 'in:get,on,off'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $idLogger = $request->input('id_logger');
        $action   = $request->input('action');

        // Pastikan user boleh mengakses logger ini dan logger punya pompa (JIAT + has_pump)
        $logger = $this->findPumpLogger($request, $idLogger);

        if (!$logger) {
            return $this->noPumpResponse();
        }

        $isControl = in_array($action, ['on', 'off'], true);

        if ($isControl && !$this->isUnlocked($request, $logger->id_logger)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN kontrol diperlukan atau sudah kedaluwarsa. Masukkan PIN kembali.',
                'step'    => 'pin_required',
            ], 403);
        }

        $log = $isControl
            ? $this->createControlLog($request, $logger, $action, 'pending', 'Perintah kontrol dikirim ke logger.')
            : null;

        try {
            $pump = $pumpService->send($idLogger, $action);
        } catch (PumpCommandException $e) {
            $log?->update([
                'status'       => $this->statusForPumpException($e),
                'message'      => $e->getMessage(),
                'completed_at' => now(),
                'metadata'     => array_merge(
                    ['source' => 'web', 'step' => $e->step],
                    $e->pump ? ['pump' => $e->pump] : [],
                ),
            ]);

            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
                'step'    => $e->step,
            ];

            if ($e->pump) {
                $payload['pump'] = $e->pump;
            }

            return response()->json($payload, $e->httpStatus);
        }

        $log?->update([
            'status'       => 'success',
            'message'      => $pump['msg'] ?? null,
            'completed_at' => now(),
            'metadata'     => ['source' => 'web', 'pump' => $pump],
        ]);

        return response()->json([
            'success' => true,
            'message' => ($pump['msg'] ?? '') ?: ((int) ($pump['state'] ?? 0) === 1 ? 'Pump ON' : 'Pump OFF'),
            'pump'    => [
                'status' => $pump['status'] ?? 'OK',
                'state'  => (int) ($pump['state'] ?? -1),
                'msg'    => $pump['msg'] ?? '',
            ],
        ]);
    }

    private function findPumpLogger(Request $request, string $idLogger): ?t_Logger
    {
        $logger = t_Logger::query()
            ->forUser($request->user())
            ->with(['jiat', 'instansi'])
            ->where('id_logger', $idLogger)
            ->first();

        return ($logger && $logger->jiat && $logger->jiat->has_pump) ? $logger : null;
    }

    private function noPumpResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Logger ini tidak memiliki fitur kontrol pompa.',
        ], 403);
    }

    private function unlockKey(string $idLogger): string
    {
        return "pump_control_unlock.{$idLogger}";
    }

    private function isUnlocked(Request $request, string $idLogger): bool
    {
        $expiresAt = (int) $request->session()->get($this->unlockKey($idLogger), 0);

        return $expiresAt > now()->timestamp;
    }

    private function createControlLog(
        Request $request,
        t_Logger $logger,
        string $action,
        string $status,
        ?string $message = null,
    ): PumpControlLog {
        $now = now();
        $completed = $status !== 'pending';

        return PumpControlLog::create([
            'user_id'      => $request->user()?->id_user,
            'instansi_id'  => $logger->instansi_id,
            'id_logger'    => $logger->id_logger,
            'action'       => $action,
            'status'       => $status,
            'message'      => $message,
            'requested_at' => $now,
            'completed_at' => $completed ? $now : null,
            'metadata'     => ['source' => 'web'],
        ]);
    }

    private function statusForPumpException(PumpCommandException $e): string
    {
        return match ($e->step) {
            'mqtt_connect' => 'mqtt_failed',
            'logger_error' => 'logger_error',
            'timeout'      => 'timeout',
            default        => 'failed',
        };
    }
}
