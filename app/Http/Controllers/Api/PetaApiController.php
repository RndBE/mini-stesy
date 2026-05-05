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
            ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat', 'nonjiat', 'kategori', 'informasi', 'fotos'])
            ->whereNotNull('idlokasi')
            ->get()
            ->map(function ($l) use ($thresholds) {
                $lat = $l->lokasi?->latitude;
                $lng = $l->lokasi?->longitude;

                // Ambil snapshot terbaru, prioritaskan tabel latest agar sinkron dengan waktu/status di UI.
                $latest = $this->resolveLatestSnapshot($l);

                // Sensor logger health
                $pHumidity = $this->findParamByAliases($l->params, ['humidity_logger', 'humidity']);
                $pBattery  = $this->findParamByAliases($l->params, ['battery_logger', 'battery']);
                $pTemp     = $this->findParamByAliases($l->params, ['temperature_logger', 'temperature']);

                $humidity = ($latest && $pHumidity?->kolom_sensor) ? ($latest->{$pHumidity->kolom_sensor} ?? null) : null;
                $battery  = ($latest && $pBattery?->kolom_sensor)  ? ($latest->{$pBattery->kolom_sensor} ?? null)  : null;
                $temp     = ($latest && $pTemp?->kolom_sensor)     ? ($latest->{$pTemp->kolom_sensor} ?? null)     : null;

                // Status online/offline
                $waktu16  = optional($l->temp16)->waktu;
                $waktu19  = optional($l->temp19)->waktu;
                $lastTime = ($latest->waktu ?? null) ?: collect([$waktu16, $waktu19])->filter()->sortDesc()->first();
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
                    'no_seluler'  => $l->no_seluler,
                    'sensor_count' => $l->sensor_count ?? $l->params->count(),
                    'informasi'   => $l->informasi ? [
                        'seri_logger'   => $l->informasi->seri_logger,
                        'serial_number' => $l->informasi->serial_number,
                        'awal_kontrak'  => $l->informasi->awal_kontrak,
                        'garansi'       => $l->informasi->garansi,
                        'imei'          => $l->informasi->imei,
                        'nama_pic'      => $l->informasi->nama_pic,
                        'no_pic'        => $l->informasi->no_pic,
                    ] : null,
                    'dokumentasi' => $l->fotos ? $l->fotos->pluck('url_foto')->map(fn($path) => asset('storage/' . $path))->toArray() : [],
                    'logger_health' => [
                        'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
                        'battery'  => is_numeric($battery) ? round($battery, 2) : null,
                        'temp'     => is_numeric($temp) ? round($temp, 1) : null,
                    ],
                    'sensor_data' => [
                        'tma'              => $this->sensorVal($l->params, $latest, ['tma', 'muka_air', 'tinggi_muka']),
                        'debit'            => $this->sensorVal($l->params, $latest, ['debit']),
                        'curah_hujan'      => $this->sensorVal($l->params, $latest, ['hujan', 'rain', 'curah']),
                        'elevasi_muka_air' => $this->sensorVal($l->params, $latest, ['elevasi_muka']),
                        'flow_velocity'    => $this->sensorVal($l->params, $latest, ['flow_velocity', 'velocity']),
                        'jarak_sensor'     => $this->sensorVal($l->params, $latest, ['jarak_sensor']),
                        'muka_air_tanah'   => $this->sensorVal($l->params, $latest, ['muka_air_tanah', 'air_tanah']),
                          'kecepatan_angin'  => $this->sensorVal($l->params, $latest, ['kecepatan_angin', 'wind_speed']),
                          'arah_angin'       => $this->sensorVal($l->params, $latest, ['arah_angin', 'wind_direction', 'wind_dir']),
                          'kecerahan'        => $this->sensorVal($l->params, $latest, ['kecerahan', 'light', 'cahaya']),
                          'arah_cahaya'      => $this->sensorVal($l->params, $latest, ['arah_cahaya', 'light_direction']),
                          'temperature'      => $this->sensorVal($l->params, $latest, ['temperature', 'suhu', 'temp']),
                          'tekanan_udara'    => $this->sensorVal($l->params, $latest, ['tekanan_udara', 'pressure', 'tekanan']),
                          'humidity'         => $this->sensorVal($l->params, $latest, ['humidity', 'kelembaban']),
                          'curah_hujan_per_jam' => $this->sensorVal($l->params, $latest, ['curah_hujan_per_jam', 'hujan_jam', 'rain_hour']),
                          'curah_hujan_harian'  => $this->sensorVal($l->params, $latest, ['curah_hujan_harian', 'hujan_hari', 'rain_day']),
                          'luas_penampang_basah' => $this->sensorVal($l->params, $latest, ['luas_penampang', 'penampang_basah', 'luas']),
                          'elevasi_sensor'   => $this->sensorVal($l->params, $latest, ['elevasi_sensor','tinggi_sensor']),
                          'orp'              => $this->sensorVal($l->params, $latest, ['orp']),
                          'ph_air'           => $this->sensorVal($l->params, $latest, ['ph', 'ph_air']),
                          'suhu_air'         => $this->sensorVal($l->params, $latest, ['suhu_air', 'water_temp']),
                          'conductivity'     => $this->sensorVal($l->params, $latest, ['conductivity', 'konduktivitas']),
                          'salinity'         => $this->sensorVal($l->params, $latest, ['salinity', 'salinitas']),
                          'turbidity'        => $this->sensorVal($l->params, $latest, ['turbidity', 'kekeruhan']),
                          'tds'              => $this->sensorVal($l->params, $latest, ['tds', 'total_dissolved_solids', 'dissolved_solids']),
                    ],
                    'sub_kategori' => $l->jiat ? 'jiat' : ($l->nonjiat ? 'non_jiat' : null),
                    'jiat_data' => $l->jiat ? [
                        'kedalaman_sumur'  => is_numeric($l->jiat->kedalaman_sumur)  ? (float) $l->jiat->kedalaman_sumur  : null,
                        'kedalaman_sensor' => is_numeric($l->jiat->kedalaman_sensor) ? (float) $l->jiat->kedalaman_sensor : null,
                        'kedalaman_pompa'  => is_numeric($l->jiat->kedalaman_pompa)  ? (float) $l->jiat->kedalaman_pompa  : null,
                        'has_pump'         => (bool) ($l->jiat->has_pump ?? false),
                    ] : null,
                    'nonjiat_data' => $l->nonjiat ? [
                        'elevasi_min'          => is_numeric($l->nonjiat->elevasi_min)          ? (float) $l->nonjiat->elevasi_min          : null,
                        'elevasi_max'          => is_numeric($l->nonjiat->elevasi_max)          ? (float) $l->nonjiat->elevasi_max          : null,
                        'tinggi_sensor'        => is_numeric($l->nonjiat->tinggi_sensor)        ? (float) $l->nonjiat->tinggi_sensor        : null,
                        'jarak_sensor_ke_air'  => is_numeric($l->nonjiat->jarak_sensor_ke_air)  ? (float) $l->nonjiat->jarak_sensor_ke_air  : null,
                    ] : null,
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

    private function findParamByAliases($params, array $aliases): mixed
    {
        $aliases = collect($aliases)
            ->map(fn($alias) => strtolower(trim((string) $alias)))
            ->filter()
            ->values()
            ->all();

        return $params->first(function ($param) use ($aliases) {
            $name = strtolower(trim((string) $param->nama_parameter));
            $utama = strtolower(trim((string) $param->parameter_utama));

            return in_array($name, $aliases, true) || in_array($utama, $aliases, true);
        });
    }

    private function resolveLatestSnapshot($logger): mixed
    {
        $latestTemp = collect([$logger->temp16, $logger->temp19])
            ->filter(fn($row) => $row && !empty($row->waktu))
            ->sortByDesc(fn($row) => (string) $row->waktu)
            ->first();

        if ($latestTemp) {
            return $latestTemp;
        }

        $pTempS16 = $logger->params->firstWhere('kolom_sensor', 'temp_s16');
        $pTempS19 = $logger->params->firstWhere('kolom_sensor', 'temp_s19');
        $timeColumn = $pTempS19 ? 'temp_s19' : ($pTempS16 ? 'temp_s16' : null);

        if (!$logger->tabel_main) {
            return null;
        }

        if ($timeColumn) {
            $latest = DB::table($logger->tabel_main)
                ->where('id_logger', $logger->id_logger)
                ->whereNotNull($timeColumn)
                ->orderByDesc($timeColumn)
                ->first();

            if ($latest) {
                return $latest;
            }
        }

        $latest = DB::table($logger->tabel_main)
            ->where('id_logger', $logger->id_logger)
            ->whereNotNull('waktu')
            ->orderByDesc('waktu')
            ->first();

        if ($latest) {
            return $latest;
        }

        return DB::table($logger->tabel_main)
            ->where('id_logger', $logger->id_logger)
            ->orderByDesc('id')
            ->first();
    }
}
