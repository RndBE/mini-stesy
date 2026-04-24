<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

/**
 * PumpCommandController
 *
 * Mengirim perintah kontrol pompa AWLR JIAT via MQTT dan menunggu respon logger.
 *
 * Protocol:
 *   Publish ke logger  : sub_{id_logger}
 *   Subscribe balasan  : pub_{id_logger}
 *
 * Commands:
 *   GET status : {"AWLR_PUMP": {"cmd": "GET"}}
 *   SET ON     : {"AWLR_PUMP": {"cmd": "SET", "state": 1}}
 *   SET OFF    : {"AWLR_PUMP": {"cmd": "SET", "state": 0}}
 *
 * Response dari logger:
 *   {"AWLR_PUMP": {"status": "OK", "state": 1|0, "msg": "Pump ON|OFF"}}
 */
class PumpCommandController extends Controller
{
    /**
     * Timeout menunggu balasan dari logger (detik).
     */
    private const SUBSCRIBE_TIMEOUT = 10;

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
        $payload  = $this->buildPayload($action);
        $pubTopic = "sub_{$idLogger}";  // Kirim perintah KE logger
        $subTopic = "pub_{$idLogger}";  // Terima balasan DARI logger

        $user = Auth::user();
        Log::info("Pump command [{$action}] by {$user?->name} for logger {$idLogger}");

        try {
            $mqtt = $this->createMqttClient();

            // Subscribe dulu agar tidak miss balasan cepat
            $response = null;

            $mqtt->subscribe($subTopic, function (string $topic, string $message) use (&$response, $mqtt) {
                Log::info("Pump response on {$topic}: {$message}");

                $decoded = json_decode($message, true);

                if (isset($decoded['AWLR_PUMP'])) {
                    $response = $decoded['AWLR_PUMP'];
                    $mqtt->interrupt();
                }
            }, 1);

            // Publish command ke logger
            $mqtt->publish($pubTopic, json_encode($payload), 1);
            Log::info("Pump command published to {$pubTopic}: " . json_encode($payload));

            // Loop menunggu balasan dari logger
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

        // Logger tidak merespons
        if ($response === null) {
            return response()->json([
                'success' => false,
                'message' => 'Logger tidak merespons dalam waktu yang ditentukan.',
                'step'    => 'timeout',
            ], 504);
        }

        // Logger merespons — evaluasi hasilnya
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
     * Buat koneksi MQTT client dengan handling TLS/non-TLS dari .env.
     */
    private function createMqttClient(): MqttClient
    {
        $config = config('mqtt-client.connections.default');

        $host     = $config['host'] ?? 'mqtt.beacontelemetry.com';
        $port     = (int) ($config['port'] ?? 8883);
        $clientId = $config['client_id'] ?? ('pump_ctrl_' . uniqid());

        $connSettings = $config['connection_settings'] ?? [];
        $tlsConfig    = $connSettings['tls'] ?? [];
        $authConfig   = $connSettings['auth'] ?? [];

        $username = $authConfig['username'] ?? env('MQTT_USER', 'userlog');
        $password = $authConfig['password'] ?? env('MQTT_PASS', 'b34c0n');

        $tlsEnabled = (bool) ($tlsConfig['enabled'] ?? true);

        $settings = (new ConnectionSettings)
            ->setUsername($username)
            ->setPassword($password)
            ->setConnectTimeout((int) ($connSettings['connect_timeout'] ?? 5))
            ->setSocketTimeout((int) ($connSettings['socket_timeout'] ?? 3))
            ->setKeepAliveInterval((int) ($connSettings['keep_alive_interval'] ?? 10));

        if ($tlsEnabled) {
            $caFile     = $tlsConfig['ca_file'] ?? null;
            $verifyPeer = true;

            if ($caFile && !is_file($caFile)) {
                $systemCa = $this->findSystemCaFile();
                if ($systemCa) {
                    $caFile = $systemCa;
                } else {
                    $caFile     = null;
                    $verifyPeer = false;
                }
            }

            $settings = $settings->setUseTls(true)
                ->setTlsVerifyPeer($verifyPeer)
                ->setTlsVerifyPeerName($verifyPeer);

            if ($caFile && is_file($caFile)) {
                $settings = $settings->setTlsCertificateAuthorityFile($caFile);
            }

            if (!$verifyPeer) {
                $settings = $settings->setTlsSelfSignedAllowed(true);
            }
        }

        $mqtt = new MqttClient($host, $port, $clientId, MqttClient::MQTT_3_1_1);
        $mqtt->connect($settings, (bool) ($config['use_clean_session'] ?? true));

        return $mqtt;
    }

    /**
     * Cari CA bundle system (macOS / Linux).
     */
    private function findSystemCaFile(): ?string
    {
        $candidates = [];

        if (function_exists('openssl_get_cert_locations')) {
            $loc = openssl_get_cert_locations();
            if (!empty($loc['default_cert_file'])) {
                $candidates[] = $loc['default_cert_file'];
            }
        }

        $candidates = array_merge($candidates, [
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
            '/etc/ssl/cert.pem',
            '/usr/local/etc/openssl/cert.pem',
            '/opt/homebrew/etc/openssl/cert.pem',
        ]);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
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
