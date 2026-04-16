<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\KlasifikasiThreshold;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Services\MiniStesyApi;

class PetaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all thresholds grouped by kategori
        $thresholds = KlasifikasiThreshold::with('kategori')
            ->orderBy('id_kategori')->orderBy('sort_order')
            ->get()
            ->groupBy(function($item) {
                return $item->kategori?->nama_kategori ?? 'UNKNOWN';
            });
        $points = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'temp16', 'temp19', 'jiat', 'nonjiat', 'klasifikasiHujan', 'kategori'])
            ->whereNotNull('idlokasi')
            ->get()
            ->map(function ($l) use ($thresholds) {

                $lat = $l->lokasi?->latitude;
                $lng = $l->lokasi?->longitude;

                $latest = $this->resolveLatestSnapshot($l);

                $pHumidity = $this->findParamByAliases($l->params, ['humidity_logger', 'humidity']);
                $pBattery  = $this->findParamByAliases($l->params, ['battery_logger', 'battery']);
                $pTemp     = $this->findParamByAliases($l->params, ['temperature_logger', 'temperature']);

                $humidity = ($latest && $pHumidity?->kolom_sensor) ? ($latest->{$pHumidity->kolom_sensor} ?? null) : null;
                $battery  = ($latest && $pBattery?->kolom_sensor) ? ($latest->{$pBattery->kolom_sensor} ?? null) : null;
                $temp     = ($latest && $pTemp?->kolom_sensor) ? ($latest->{$pTemp->kolom_sensor} ?? null) : null;

                $waktu16 = optional($l->temp16)->waktu;
                $waktu19 = optional($l->temp19)->waktu;

                $lastTime = ($latest->waktu ?? null) ?: collect([$waktu16, $waktu19])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $minutesDiff = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
                $status = ($minutesDiff !== null && $minutesDiff < 60) ? 'online' : 'offline';

                $kategori = $l->kategori?->kode ?? $l->kategori?->nama_kategori ?? $l->kategori?->nama ?? null;
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

                    'kategori'  => $kategori,
                    'arr_state' => $arrState,

                    'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
                    'battery'  => is_numeric($battery) ? round($battery, 2) : null,
                    'temp'     => is_numeric($temp) ? round($temp, 1) : null,

                    'status'   => $status,
                    'last_time' => $lastTime,
                    'kedalaman_sumur' => $l->jiat?->kedalaman_sumur,

                    // ── AWLR Non-JIAT ──────────────────────────────────────────────
                    'sub_kategori' => $l->nonjiat ? 'non_jiat' : ($l->jiat && (float)($l->jiat->kedalaman_sumur ?? 0) > 0 ? 'jiat' : 'non_jiat'),
                    'tma'          => $this->sensorVal($l->params, $latest, ['tma', 'muka_air', 'tinggi_muka', 'water_level']),
                    'debit'        => $this->sensorVal($l->params, $latest, ['debit']),

                    // ── AFMR ──────────────────────────────────────────────────────
                    'luas_penampang'   => $this->sensorVal($l->params, $latest, ['luas', 'penampang']),
                    'flow_velocity'    => $this->sensorVal($l->params, $latest, ['flow_velocity', 'velocity', 'kecepatan_aliran']),
                    'elevasi_muka_air' => $this->sensorVal($l->params, $latest, ['elevasi_muka']),
                    'jarak_sensor'     => $this->sensorVal($l->params, $latest, ['jarak_sensor']),
                    'elevasi_sensor'   => $this->sensorVal($l->params, $latest, ['elevasi_sensor', 'tinggi_sensor']),

                    // ── ARR / AWR – atmosfer ─────────────────────────────────────
                    'curah_hujan'     => $this->sensorVal($l->params, $latest, ['hujan', 'rain', 'curah']),
                    'curah_hujan_2'   => $this->sensorValNth($l->params, $latest, ['hujan', 'rain', 'curah'], 2),
                    'kecepatan_angin' => $this->sensorVal($l->params, $latest, ['kecepatan_angin', 'wind_speed', 'speed_angin']),
                    'arah_angin'      => $this->sensorVal($l->params, $latest, ['arah_angin', 'wind_dir', 'direction_angin']),
                    'kecerahan'       => $this->sensorVal($l->params, $latest, ['kecerahan', 'brightness', 'cahaya_terang', 'light']),
                    'arah_cahaya'     => $this->sensorVal($l->params, $latest, ['arah_cahaya', 'direction_light', 'light_dir']),
                    'temperatur_udara'=> $this->sensorVal($l->params, $latest, ['suhu_udara', 'temperatur_udara', 'temperature_air']),
                    'kelembaban_udara'=> $this->sensorVal($l->params, $latest, ['kelembaban_udara', 'humidity_udara', 'rh_udara']),
                    'tekanan_udara'   => $this->sensorVal($l->params, $latest, ['tekanan', 'pressure', 'barometric']),

                    // ── AWQR ─────────────────────────────────────────────────────
                    'ph_air'      => $this->sensorVal($l->params, $latest, ['ph']),
                    'suhu_air'    => $this->sensorVal($l->params, $latest, ['suhu_air', 'water_temp', 'temp_air']),
                    'orp'         => $this->sensorVal($l->params, $latest, ['orp']),
                    'conductivity'=> $this->sensorVal($l->params, $latest, ['conduct', 'konduktivitas', 'conductivity']),
                    'salinity'    => $this->sensorVal($l->params, $latest, ['salinity', 'salinitas']),
                    'tds'         => $this->sensorVal($l->params, $latest, ['tds', 'total_dissolved', 'dissolved_solid']),
                    'turbidity'   => $this->sensorVal($l->params, $latest, ['turbidity', 'kekeruhan', 'turbiditas']),
                    'tinggi_sensor_awqr' => $this->sensorVal($l->params, $latest, ['tinggi_sensor', 'elevasi_sensor', 'sensor_height']),
                ];
            })
            ->filter(fn($p) => $p['lat'] !== null && $p['lng'] !== null)
            ->values();

        return view('peta.index', [
            'title'  => 'Peta Lokasi',
            'points' => $points,
            'thresholds' => $thresholds,
        ]);
    }
    // public function index(MiniStesyApi $api)
    // {
    //     $lokasi = $api->semuaLokasi();

    //     $points = collect($lokasi)->map(function ($l) {
    //         return [
    //             'id_logger' => $l['id_logger'],
    //             'nama'      => $l['lokasi'],
    //             'lat'       => (float) $l['latitude'],
    //             'lng'       => (float) $l['longitude'],
    //             'status'    => strtolower($l['koneksi_log']) === 'on' ? 'online' : 'offline',
    //             'waktu'     => $l['waktu'],
    //         ];
    //     })->values();

    //     return view('peta.index', compact('points'));
    // }

    /**
     * Get classification state from dynamic thresholds
     */
    private function getStateFromThreshold($logger, string $status, $latest, $thresholdCollection): string
    {
        // Jika offline → selalu koneksi_terputus
        if ($status === 'offline') {
            return $thresholdCollection->firstWhere('state_key', 'koneksi_terputus')
                ?->state_key ?? 'koneksi_terputus';
        }

        // Cari parameter curah hujan
        $pRain = $logger->params->first(function ($p) {
            $n = strtolower(trim((string) $p->nama_parameter));
            $u = strtolower(trim((string) $p->parameter_utama));
            return $n === 'curah hujan' || $u === 'hujan';
        });

        $col   = $pRain?->kolom_sensor;
        $value = null;

        if ($latest && $col && isset($latest->{$col})) {
            $value = is_numeric($latest->{$col}) ? (float) $latest->{$col} : null;
        }

        // Jika tidak ada parameter hujan (misal logger AWR) → gunakan state online default
        if (!$pRain) {
            // Cari state 'online' jika ada, atau gunakan state pertama yang bukan koneksi_terputus
            $onlineState = $thresholdCollection->firstWhere('state_key', 'online')
                ?? $thresholdCollection->filter(fn($t) => $t->state_key !== 'koneksi_terputus' && $t->state_key !== 'perbaikan')
                    ->sortBy('sort_order')->first();
            return $onlineState?->state_key ?? $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'online';
        }

        // Jika tidak ada nilai hujan → state pertama (biasanya tidak_hujan / awr_sangat_ringan)
        if ($value === null) {
            return $thresholdCollection->filter(fn($t) => $t->min_value !== null || $t->max_value !== null)
                ->sortBy('sort_order')->first()?->state_key
                ?? $thresholdCollection->sortBy('sort_order')->first()?->state_key
                ?? 'tidak_hujan';
        }

        // Cocokkan nilai ke threshold
        foreach ($thresholdCollection->sortBy('sort_order') as $threshold) {
            if ($threshold->min_value === null && $threshold->max_value === null) {
                continue;
            }
            $matchesMin = $threshold->min_value === null || $value >= $threshold->min_value;
            $matchesMax = $threshold->max_value === null || $value < $threshold->max_value;
            if ($matchesMin && $matchesMax) {
                return $threshold->state_key;
            }
        }

        return $thresholdCollection->sortBy('sort_order')->first()?->state_key ?? 'tidak_hujan';
    }

    /**
     * Cari nilai sensor berdasarkan keyword dalam nama_parameter (ambil kemunculan pertama).
     */
    private function sensorVal($params, $latest, array $keywords, int $nth = 1): mixed
    {
        if (!$latest) return null;

        $count = 0;
        foreach ($params as $p) {
            $name = strtolower(trim((string) $p->nama_parameter));
            foreach ($keywords as $kw) {
                if (str_contains($name, strtolower($kw))) {
                    $count++;
                    if ($count === $nth) {
                        $col = $p->kolom_sensor;
                        if ($col && isset($latest->{$col})) {
                            $v = $latest->{$col};
                            return is_numeric($v) ? round((float) $v, 3) : null;
                        }
                    }
                    break; // match keyword, next param
                }
            }
        }
        return null;
    }

    /**
     * Ambil kemunculan ke-N sensor berdasarkan keyword (untuk Curah Hujan 2, dsb).
     */
    private function sensorValNth($params, $latest, array $keywords, int $nth): mixed
    {
        return $this->sensorVal($params, $latest, $keywords, $nth);
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
