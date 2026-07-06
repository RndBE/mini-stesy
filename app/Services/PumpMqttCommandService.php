<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;
use Throwable;

class PumpMqttCommandService
{
    private const SUBSCRIBE_TIMEOUT = 10;

    public function send(string $idLogger, string $action): array
    {
        $payload = $this->buildPayload($action);
        $pubTopic = "sub_{$idLogger}";
        $subTopic = "pub_{$idLogger}";

        try {
            $mqtt = $this->createMqttClient();
            $response = null;

            $mqtt->subscribe($subTopic, function (string $topic, string $message) use (&$response, $mqtt) {
                Log::info("Pump response on {$topic}: {$message}");

                $decoded = json_decode($message, true);

                if (isset($decoded['GCM_PUMP'])) {
                    $response = $decoded['GCM_PUMP'];
                    $mqtt->interrupt();
                } elseif (isset($decoded['AWLR_PUMP'])) {
                    $response = $decoded['AWLR_PUMP'];
                    $mqtt->interrupt();
                }
            }, 1);

            $mqtt->publish($pubTopic, json_encode($payload), 1);
            Log::info("Pump command published to {$pubTopic}: " . json_encode($payload));

            $mqtt->registerLoopEventHandler(function ($mqtt, float $elapsedTime) {
                if ($elapsedTime >= self::SUBSCRIBE_TIMEOUT) {
                    $mqtt->interrupt();
                }
            });

            $mqtt->loop(true);
            $mqtt->disconnect();
        } catch (Throwable $e) {
            Log::error("Pump MQTT error for logger {$idLogger}: " . $e->getMessage());

            throw new PumpCommandException(
                'Gagal terhubung ke MQTT broker: ' . $e->getMessage(),
                503,
                'mqtt_connect',
                previous: $e,
            );
        }

        if ($response === null) {
            throw new PumpCommandException(
                'Logger tidak merespons dalam waktu yang ditentukan.',
                504,
                'timeout',
            );
        }

        $pumpStatus = $response['status'] ?? 'UNKNOWN';
        $pumpState = (int) ($response['state'] ?? -1);
        $pumpMsg = $response['msg'] ?? '';

        if ($pumpStatus !== 'OK') {
            throw new PumpCommandException(
                "Logger merespons dengan error: {$pumpMsg}",
                502,
                'logger_error',
                $response,
            );
        }

        return [
            'status' => $pumpStatus,
            'state' => $pumpState,
            'msg' => $pumpMsg,
        ];
    }

    private function createMqttClient(): MqttClient
    {
        $config = config('mqtt-client.connections.default');

        $host = $config['host'] ?? 'mqtt.beacontelemetry.com';
        $port = (int) ($config['port'] ?? 8883);
        $clientId = $config['client_id'] ?? ('pump_ctrl_' . uniqid());

        $connSettings = $config['connection_settings'] ?? [];
        $tlsConfig = $connSettings['tls'] ?? [];
        $authConfig = $connSettings['auth'] ?? [];

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
            $caFile = $tlsConfig['ca_file'] ?? null;
            $verifyPeer = true;

            if ($caFile && !is_file($caFile)) {
                $systemCa = $this->findSystemCaFile();
                if ($systemCa) {
                    $caFile = $systemCa;
                } else {
                    $caFile = null;
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

    private function buildPayload(string $action): array
    {
        return match ($action) {
            'get' => [
                'GCM_PUMP' => ['cmd' => 'GET', 'id' => 1],
            ],
            'on' => [
                'GCM_PUMP' => ['cmd' => 'SET', 'id' => 1, 'state' => 1],
            ],
            'off' => [
                'GCM_PUMP' => ['cmd' => 'SET', 'id' => 1, 'state' => 0],
            ],
        };
    }
}
