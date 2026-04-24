<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpMqtt\Client\Facades\MQTT;

/**
 * PumpCommandController
 *
 * Mengirim perintah kontrol pompa AWLR JIAT via MQTT.
 *
 * Protocol:
 *   Publish  : pub_{id_logger}
 *   Subscribe: sub_{id_logger}
 *
 * Commands:
 *   GET status : {"AWLR_PUMP": {"cmd": "GET"}}
 *   SET ON     : {"AWLR_PUMP": {"cmd": "SET", "state": 1}}
 *   SET OFF    : {"AWLR_PUMP": {"cmd": "SET", "state": 0}}
 *
 * Response:
 *   {"AWLR_PUMP": {"status": "OK", "state": 1|0, "msg": "Pump ON|OFF"}}
 */
class PumpCommandController extends Controller
{
    /**
     * Timeout menunggu balasan dari logger (detik).
     */
    private const SUBSCRIBE_TIMEOUT = 8;

    /**
     * POST /api/pump/command
     *
     * Body:
     *   id_logger : string (required)
     *   action    : "get" | "on" | "off" (required)
     */
    public function command(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_logger' => 'required|string|exists:t_logger,id_logger',
            'action'    => 'required|string|in:get,on,off',
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

        // Pastikan logger ini punya pompa (JIAT + has_pump)
        $logger = t_Logger::with('jiat')->where('id_logger', $idLogger)->first();

        if (!$logger || !$logger->jiat || !$logger->jiat->has_pump) {
            return response()->json([
                'success' => false,
                'message' => 'Logger ini tidak memiliki fitur kontrol pompa.',
            ], 403);
        }

        // Build MQTT payload
        $payload = $this->buildPayload($action);

        $pubTopic = "pub_{$idLogger}";
        $subTopic = "sub_{$idLogger}";

        $user = Auth::user();
        Log::info("Pump command [{$action}] by {$user?->name} for logger {$idLogger}");

        try {
            $mqtt = MQTT::connection();

            // Subscribe dulu agar tidak miss balasan cepat
            $response = null;

            $mqtt->subscribe($subTopic, function (string $topic, string $message) use (&$response, $mqtt) {
                Log::info("Pump response received on {$topic}: {$message}");

                $decoded = json_decode($message, true);

                if (isset($decoded['AWLR_PUMP'])) {
                    $response = $decoded['AWLR_PUMP'];
                    $mqtt->interrupt(); // Keluar dari loop
                }
            }, 1);

            // Publish command
            $mqtt->publish($pubTopic, json_encode($payload), 1);
            Log::info("Pump command published to {$pubTopic}: " . json_encode($payload));

            // Loop menunggu balasan, max SUBSCRIBE_TIMEOUT detik
            $mqtt->registerLoopEventHandler(function ($mqtt, float $elapsedTime) {
                if ($elapsedTime >= self::SUBSCRIBE_TIMEOUT) {
                    $mqtt->interrupt();
                }
            });

            $mqtt->loop(true);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            Log::error("Pump MQTT error for logger {$idLogger}: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke MQTT broker: ' . $e->getMessage(),
                'step'    => 'mqtt_connect',
            ], 503);
        }

        // Evaluasi response
        if ($response === null) {
            return response()->json([
                'success' => false,
                'message' => 'Logger tidak merespons dalam waktu yang ditentukan.',
                'step'    => 'timeout',
            ], 504);
        }

        $pumpStatus = $response['status'] ?? 'UNKNOWN';
        $pumpState  = (int) ($response['state'] ?? -1);
        $pumpMsg    = $response['msg'] ?? '';

        if ($pumpStatus !== 'OK') {
            return response()->json([
                'success' => false,
                'message' => "Logger merespons dengan error: {$pumpMsg}",
                'step'    => 'logger_error',
                'pump'    => $response,
            ], 502);
        }

        return response()->json([
            'success' => true,
            'message' => $pumpMsg ?: ($pumpState === 1 ? 'Pump ON' : 'Pump OFF'),
            'pump'    => [
                'status' => $pumpStatus,
                'state'  => $pumpState,
                'msg'    => $pumpMsg,
            ],
        ]);
    }

    /**
     * Build MQTT payload sesuai protocol.
     */
    private function buildPayload(string $action): array
    {
        return match ($action) {
            'get' => [
                'AWLR_PUMP' => ['cmd' => 'GET'],
            ],
            'on' => [
                'AWLR_PUMP' => ['cmd' => 'SET', 'state' => 1],
            ],
            'off' => [
                'AWLR_PUMP' => ['cmd' => 'SET', 'state' => 0],
            ],
        };
    }
}
