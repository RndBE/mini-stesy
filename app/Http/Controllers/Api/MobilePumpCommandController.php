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
use Illuminate\Validation\Rule;

class MobilePumpCommandController extends Controller
{
    public function command(Request $request, PumpMqttCommandService $pumpService)
    {
        $action = (string) $request->input('action');
        $requiresAuthorization = in_array($action, ['on', 'off'], true);

        $validator = Validator::make($request->all(), [
            'id_logger' => ['required', 'string'],
            'action' => ['required', 'string', 'in:get,on,off'],
            'control_pin' => [Rule::requiredIf($requiresAuthorization), 'string', 'max:32'],
            'latitude' => [Rule::requiredIf($requiresAuthorization), 'numeric', 'between:-90,90'],
            'longitude' => [Rule::requiredIf($requiresAuthorization), 'numeric', 'between:-180,180'],
            'location_permission_status' => [
                Rule::requiredIf($requiresAuthorization),
                'string',
                'in:granted',
            ],
        ], [
            'location_permission_status.in' => 'Izin lokasi wajib diberikan sebelum kontrol pompa.',
            'location_permission_status.required' => 'Izin lokasi wajib diberikan sebelum kontrol pompa.',
            'latitude.required' => 'Lokasi wajib dikirim sebelum kontrol pompa.',
            'longitude.required' => 'Lokasi wajib dikirim sebelum kontrol pompa.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $idLogger = $validated['id_logger'];
        $action = $validated['action'];

        $logger = t_Logger::query()
            ->forUser($request->user())
            ->with(['jiat', 'instansi'])
            ->where('id_logger', $idLogger)
            ->first();

        if (!$logger || !$logger->jiat || !$logger->jiat->has_pump) {
            return response()->json([
                'success' => false,
                'message' => 'Logger ini tidak memiliki fitur kontrol pompa.',
            ], 403);
        }

        if (in_array($action, ['on', 'off'], true)) {
            $pinCheck = $this->authorizeControlPin($request, $logger, $action, $validated);

            if ($pinCheck !== null) {
                return $pinCheck;
            }
        }

        $log = null;
        if (in_array($action, ['on', 'off'], true)) {
            $log = $this->createControlLog($request, $logger, $action, 'pending', 'Perintah kontrol dikirim ke logger.');
        }

        try {
            $pump = $pumpService->send($idLogger, $action);
        } catch (PumpCommandException $e) {
            if ($log) {
                $log->update([
                    'status' => $this->statusForPumpException($e),
                    'message' => $e->getMessage(),
                    'completed_at' => now(),
                    'metadata' => $e->pump ? ['pump' => $e->pump, 'step' => $e->step] : ['step' => $e->step],
                ]);
            }

            $payload = [
                'success' => false,
                'message' => $e->getMessage(),
                'step' => $e->step,
            ];

            if ($e->pump) {
                $payload['pump'] = $e->pump;
            }

            return response()->json($payload, $e->httpStatus);
        }

        if ($log) {
            $log->update([
                'status' => 'success',
                'message' => $pump['msg'] ?? null,
                'completed_at' => now(),
                'metadata' => ['pump' => $pump],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => ($pump['msg'] ?? '') ?: ((int) ($pump['state'] ?? 0) === 1 ? 'Pump ON' : 'Pump OFF'),
            'pump' => [
                'status' => $pump['status'] ?? 'OK',
                'state' => (int) ($pump['state'] ?? -1),
                'msg' => $pump['msg'] ?? '',
            ],
        ]);
    }

    private function authorizeControlPin(Request $request, t_Logger $logger, string $action, array $validated)
    {
        $instansi = $logger->instansi;

        if (!$instansi || !$instansi->has_control_pin) {
            $this->createControlLog(
                $request,
                $logger,
                $action,
                'pin_not_configured',
                'PIN kontrol belum dikonfigurasi untuk instansi ini.',
                completed: true,
            );

            return response()->json([
                'success' => false,
                'message' => 'PIN kontrol belum dikonfigurasi untuk instansi ini.',
            ], 403);
        }

        if (!Hash::check((string) $validated['control_pin'], (string) $instansi->control_pin_hash)) {
            $this->createControlLog(
                $request,
                $logger,
                $action,
                'pin_failed',
                'PIN kontrol tidak sesuai.',
                completed: true,
            );

            return response()->json([
                'success' => false,
                'message' => 'PIN kontrol tidak sesuai.',
            ], 403);
        }

        return null;
    }

    private function createControlLog(
        Request $request,
        t_Logger $logger,
        string $action,
        string $status,
        ?string $message = null,
        bool $completed = false,
    ): PumpControlLog {
        $now = now();

        return PumpControlLog::create([
            'user_id' => $request->user()?->id_user,
            'instansi_id' => $logger->instansi_id,
            'id_logger' => $logger->id_logger,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'location_permission_status' => $request->input('location_permission_status'),
            'requested_at' => $now,
            'completed_at' => $completed ? $now : null,
        ]);
    }

    private function statusForPumpException(PumpCommandException $e): string
    {
        return match ($e->step) {
            'mqtt_connect' => 'mqtt_failed',
            'logger_error' => 'logger_error',
            'timeout' => 'timeout',
            default => 'failed',
        };
    }
}
