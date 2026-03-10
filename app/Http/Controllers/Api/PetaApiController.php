<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\t_Logger;
use App\Models\KlasifikasiThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PetaApiController extends Controller
{
    /**
     * GET /api/v1/mobile/peta/points
     * Semua logger dengan koordinat, status, dan data sensor.
     */
    public function points(Request $request)
    {
        $thresholds = KlasifikasiThreshold::with('kategori')
            ->orderBy('id_kategori')->orderBy('sort_order')
            ->get()
            ->groupBy(fn($item) => $item->kategori?->nama_kategori ?? 'UNKNOWN');

        $points = t_Logger::query()
            ->forUser($request->user())
            ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat', 'nonjiat', 'kategori'])
            ->whereNotNull('idlokasi')
            ->get()
            ->map(function ($l) use ($thresholds) {
                $lat = $l->lokasi?->latitude;
                $lng = $l->lokasi?->longitude;

                // Tentukan kolom waktu
                $pTempS16 = $l->params->firstWhere('kolom_sensor', 'temp_s16');
                $pTempS19 = $l->params->firstWhere('kolom_sensor', 'temp_s19');
                $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : null);

                // Ambil data terbaru
                $latest = null;
                if ($l->tabel_main) {
                    if ($timeColumn) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->whereNotNull($timeColumn)
                            ->orderByDesc($timeColumn)
                            ->first();
                    }
                    if (!$latest) {
                        $latest = DB::table($l->tabel_main)
                            ->where('id_logger', $l->id_logger)
                            ->whereNotNull('waktu')
                            ->orderByDesc('waktu')
                            ->first();
                    }
                }

                // Sensor logger health
                $pHumidity = $l->params->firstWhere('nama_parameter', 'humidity_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'humidity');
                $pBattery  = $l->params->firstWhere('nama_parameter', 'battery_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'battery');
                $pTemp     = $l->params->firstWhere('nama_parameter', 'temperature_logger')
                    ?? $l->params->firstWhere('nama_parameter', 'temperature');

                $humidity = ($latest && $pHumidity?->kolom_sensor) ? ($latest->{$pHumidity->kolom_sensor} ?? null) : null;
                $battery  = ($latest && $pBattery?->kolom_sensor)  ? ($latest->{$pBattery->kolom_sensor} ?? null)  : null;
                $temp     = ($latest && $pTemp?->kolom_sensor)     ? ($latest->{$pTemp->kolom_sensor} ?? null)     : null;

                // Status online/offline
                $waktu16  = optional($l->temp16)->waktu;
                $waktu19  = optional($l->temp19)->waktu;
                $lastTime = collect([$waktu16, $waktu19])->filter()->sortDesc()->first();
                $diffMin  = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
                $status   = ($diffMin !== null && $diffMin < 60) ? 'online' : 'offline';

                $kategori = $l->kategori?->kode ?? $l->kategori?->nama_kategori ?? null;
                $kategori = $kategori ? strtoupper(trim($kategori)) : null;

                $arrState = null;
                if ($kategori && isset($thresholds[$kategori])) {
                    $arrState = $this->getStateFromThreshold($l, $status, $latest, $thresholds[$kategori]);
                }

                return [
                    'id_logger'   => $l->id_logger,
                    'nama_logger' => $l->nama_logger,
                    'nama_lokasi' => $l->lokasi?->nama_lokasi,
                    'lat'         => $lat !== null ? (float) $lat : null,
                    'lng'         => $lng !== null ? (float) $lng : null,
                    'kategori'    => $kategori,
                    'arr_state'   => $arrState,
                    'status'      => $status,
                    'last_time'   => $lastTime,
                    'logger_health' => [
                        'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
                        'battery'  => is_numeric($battery) ? round($battery, 2) : null,
                        'temp'     => is_numeric($temp) ? round($temp, 1) : null,
                    ],
                    'sensor_data' => [
                        'tma'            => $this->sensorVal($l->params, $latest, ['tma', 'muka_air', 'tinggi_muka']),
                        'debit'          => $this->sensorVal($l->params, $latest, ['debit']),
                        'curah_hujan'    => $this->sensorVal($l->params, $latest, ['hujan', 'rain', 'curah']),
                        'elevasi_muka_air' => $this->sensorVal($l->params, $latest, ['elevasi_muka']),
                        'flow_velocity'  => $this->sensorVal($l->params, $latest, ['flow_velocity', 'velocity']),
                        'jarak_sensor'   => $this->sensorVal($l->params, $latest, ['jarak_sensor']),
                    ],
                ];
            })
            ->filter(fn($p) => $p['lat'] !== null && $p['lng'] !== null)
            ->values();

        return response()->json([
            'success' => true,
            'data'    => $points,
            'meta'    => ['total' => $points->count(), 'generated_at' => now()->toIso8601String()],
        ]);
    }

    private function getStateFromThreshold($logger, string $status, $latest, $thresholdCollection): string
    {
        if ($status === 'offline') {
            return $thresholdCollection->firstWhere('state_key', 'koneksi_terputus')?->state_key ?? 'koneksi_terputus';
        }
        $pRain = $logger->params->first(fn($p) => str_contains(strtolower($p->nama_parameter ?? ''), 'hujan') || str_contains(strtolower($p->parameter_utama ?? ''), 'hujan'));
        $col   = $pRain?->kolom_sensor;
        $value = ($latest && $col && isset($latest->{$col})) ? (float) $latest->{$col} : null;

        if (!$pRain) {
            return $thresholdCollection->firstWhere('state_key', 'online')?->state_key ?? 'online';
        }
        if ($value === null) {
            return $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'tidak_hujan';
        }
        foreach ($thresholdCollection->sortBy('sort_order') as $t) {
            if ($t->min_value === null && $t->max_value === null) continue;
            $matchMin = $t->min_value === null || $value >= $t->min_value;
            $matchMax = $t->max_value === null || $value < $t->max_value;
            if ($matchMin && $matchMax) return $t->state_key;
        }
        return $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'tidak_hujan';
    }

    private function sensorVal($params, $latest, array $keywords): mixed
    {
        if (!$latest) return null;
        foreach ($params as $p) {
            $name = strtolower(trim((string) $p->nama_parameter));
            foreach ($keywords as $kw) {
                if (str_contains($name, strtolower($kw))) {
                    $col = $p->kolom_sensor;
                    if ($col && isset($latest->{$col})) {
                        $v = $latest->{$col};
                        return is_numeric($v) ? round((float) $v, 3) : null;
                    }
                    break;
                }
            }
        }
        return null;
    }
}
