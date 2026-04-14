<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event: SensorDataUpdated
 *
 * Di-broadcast setiap kali ada data baru masuk dari alat logger di lapangan
 * (baik AWLR maupun AWGC). Browser yang sedang membuka Skema Irigasi akan
 * menangkap event ini dan langsung memperbarui tampilan SVG secara real-time
 * tanpa perlu refresh halaman.
 *
 * Channel: sensor.data (Public Channel)
 * Listen di frontend: Echo.channel('sensor.data').listen('SensorDataUpdated', ...)
 */
class SensorDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Payload lengkap data sensor yang akan dikirimkan ke browser.
     */
    public array $payload;

    /**
     * Buat instance event baru.
     *
     * @param array $payload Data sensor yang akan dikirim ke frontend
     *   Struktur:
     *   [
     *     'node_id'       => 'BGP_1',         // ID node di Skema Irigasi SVG
     *     'id_logger'     => 'AWL-001',        // ID logger fisik
     *     'jenis_alat'    => 'AWLR',           // AWLR atau AWGC
     *     'tma'           => 152.4,            // Tinggi Muka Air (cm) - AWLR
     *     'debit'         => 1250.5,           // Debit alir (lt/dt) - keduanya
     *     'bukaan_cm'     => null,             // Posisi pintu saat ini (cm) - AWGC
     *     'bukaan_persen' => null,             // Posisi pintu saat ini (%) - AWGC
     *     'status_alat'   => 'online',         // online/offline/error
     *     'status_siaga'  => 'siaga_1',        // normal/siaga_1/siaga_2/banjir
     *     'flow_state'    => 'high',           // dry/trickle/full/high/overflow (untuk visual SVG)
     *     'waktu'         => '2026-04-06T...'  // Timestamp data
     *   ]
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Channel tujuan broadcast.
     * Menggunakan Public Channel agar semua pengunjung halaman skema bisa menerima.
     * (Untuk keamanan tambahan, bisa diganti PrivateChannel dengan auth check)
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('sensor.data'),
        ];
    }

    /**
     * Nama event yang akan di-listen di JavaScript.
     * Digunakan di: Echo.channel('sensor.data').listen('SensorDataUpdated', ...)
     */
    public function broadcastAs(): string
    {
        return 'SensorDataUpdated';
    }

    /**
     * Data yang dikirimkan ke client (browser).
     * Menambahkan timestamp server untuk perbandingan latency.
     */
    public function broadcastWith(): array
    {
        return array_merge($this->payload, [
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
