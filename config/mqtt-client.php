<?php

declare(strict_types=1);

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\Repositories\MemoryRepository;

return [

    /*
    |--------------------------------------------------------------------------
    | Default MQTT Connection
    |--------------------------------------------------------------------------
    |
    | This setting defines the default MQTT connection returned when requesting
    | a connection without name from the facade.
    |
    */

    'default_connection' => 'default',

    /*
    |--------------------------------------------------------------------------
    | MQTT Connections
    |--------------------------------------------------------------------------
    |
    | These are the MQTT connections used by the application. You can also open
    | an individual connection from the application itself, but all connections
    | defined here can be accessed via name conveniently.
    |
    */

    'connections' => [

        'default' => [

            // The host and port to which the client shall connect.
            'host' => env('MQTT_HOST', 'mqtt.beacontelemetry.com'),
            'port' => (int) env('MQTT_PORT', 8883),

            // The MQTT protocol version used for the connection.
            'protocol' => MqttClient::MQTT_3_1_1,

            // A specific client id to be used for the connection. If omitted,
            // a random client id will be generated for each new connection.
            'client_id' => env('MQTT_CLIENT_ID'),

            // Whether a clean session shall be used and requested by the client.
            'use_clean_session' => env('MQTT_CLEAN_SESSION', true),

            // Whether logging shall be enabled.
            'enable_logging' => env('MQTT_ENABLE_LOGGING', true),

            // Which logging channel to use for logs produced by the MQTT client.
            'log_channel' => env('MQTT_LOG_CHANNEL', null),

            // Defines which repository implementation shall be used.
            'repository' => MemoryRepository::class,

            // Additional settings used for the connection to the broker.
            'connection_settings' => [

                // The TLS settings used for the connection.
                'tls' => [
                    'enabled' => env('MQTT_TLS_ENABLED', true),
                    'allow_self_signed_certificate' => env('MQTT_TLS_ALLOW_SELF_SIGNED_CERT', false),
                    'verify_peer' => env('MQTT_TLS_VERIFY_PEER', true),
                    'verify_peer_name' => env('MQTT_TLS_VERIFY_PEER_NAME', true),
                    'ca_file' => env('MQTT_TLS_CA_FILE', env('MQTT_CA', storage_path('certs/ca-bundle.crt'))),
                    'ca_path' => env('MQTT_TLS_CA_PATH'),
                    'client_certificate_file' => env('MQTT_TLS_CLIENT_CERT_FILE'),
                    'client_certificate_key_file' => env('MQTT_TLS_CLIENT_CERT_KEY_FILE'),
                    'client_certificate_key_passphrase' => env('MQTT_TLS_CLIENT_CERT_KEY_PASSPHRASE'),
                    'alpn' => env('MQTT_TLS_ALPN'),
                ],

                // Credentials used for authentication and authorization.
                'auth' => [
                    'username' => env('MQTT_AUTH_USERNAME', env('MQTT_USER', 'userlog')),
                    'password' => env('MQTT_AUTH_PASSWORD', env('MQTT_PASS', 'b34c0n')),
                ],

                // Last will settings.
                'last_will' => [
                    'topic' => env('MQTT_LAST_WILL_TOPIC'),
                    'message' => env('MQTT_LAST_WILL_MESSAGE'),
                    'quality_of_service' => env('MQTT_LAST_WILL_QUALITY_OF_SERVICE', 0),
                    'retain' => env('MQTT_LAST_WILL_RETAIN', false),
                ],

                // Timeout pendek agar tidak blocking API terlalu lama
                'connect_timeout' => (int) env('MQTT_CONNECT_TIMEOUT', 5),
                'socket_timeout' => (int) env('MQTT_SOCKET_TIMEOUT', 3),
                'resend_timeout' => (int) env('MQTT_RESEND_TIMEOUT', 5),

                // Keep alive interval
                'keep_alive_interval' => (int) env('MQTT_KEEP_ALIVE_INTERVAL', 10),

                // Auto-reconnect dimatikan agar tidak hang
                'auto_reconnect' => [
                    'enabled' => false,
                    'max_reconnect_attempts' => 1,
                    'delay_between_reconnect_attempts' => 0,
                ],

            ],

        ],

    ],

];
