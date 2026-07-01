<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\KlasifikasiThreshold;
use App\Support\ArrRainStatus;
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
            ->with(['lokasi', 'params.parameterGroup', 'temp16', 'temp19', 'temp50', 'jiat', 'nonjiat', 'afmrContact', 'afmrNonContact', 'klasifikasiHujan', 'kategori'])
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
                $waktu50 = optional($l->temp50)->waktu;

                $lastTime = ($latest->waktu ?? null) ?: collect([$waktu16, $waktu19, $waktu50])
                    ->filter()
                    ->sortDesc()
                    ->first();

                $minutesDiff = $lastTime ? Carbon::parse($lastTime)->diffInMinutes(now()) : null;
                $status = ($minutesDiff !== null && $minutesDiff < 60) ? 'online' : 'offline';

                $kategori = $l->kategori?->kode ?? $l->kategori?->nama_kategori ?? $l->kategori?->nama ?? null;
                $kategori = $kategori ? strtoupper(trim($kategori)) : null;
                $isAfmr = $kategori && str_contains($kategori, 'AFMR');
                $subKategori = $isAfmr
                    ? ($l->afmrContact ? 'contact' : ($l->afmrNonContact ? 'non_contact' : null))
                    : ($l->nonjiat ? 'non_jiat' : ($l->jiat && (float)($l->jiat->kedalaman_sumur ?? 0) > 0 ? 'jiat' : 'non_jiat'));

                $arrState = null;
                if ($kategori && isset($thresholds[$kategori])) {
                    $arrState = $this->getStateFromThreshold($l, $status, $latest, $thresholds[$kategori]);
                }

                return [
                    'id_logger'   => $l->id_logger,
                    'nama_logger' => $l->nama_logger,
                    'nama_lokasi' => $l->lokasi?->nama_lokasi,
                    'nama_pos'    => $l->nama_pos,
                    'lat'         => $lat !== null ? (float) $lat : null,
                    'lng'         => $lng !== null ? (float) $lng : null,

                    'kategori'  => $kategori,
                    'arr_state' => $arrState,

                    'humidity' => is_numeric($humidity) ? round($humidity, 1) : null,
                    'battery'  => is_numeric($battery) ? round($battery, 2) : null,
                    'temp'     => is_numeric($temp) ? round($temp, 1) : null,

                    'status'   => $status,
                    'last_time' => $lastTime,
                    'parameter_groups' => $this->buildParameterGroups($l, $latest),
                    'kedalaman_sumur' => $l->jiat?->kedalaman_sumur,
                    'muka_air_tanah'  => $this->sensorVal($l->params, $latest, ['muka_air_tanah', 'muka_air', 'water_table', 'mat']),


                    // ── AWLR Non-JIAT ──────────────────────────────────────────────
                    'sub_kategori' => $subKategori,
                    'tma'          => $this->sensorVal($l->params, $latest, ['tma', 'muka_air', 'tinggi_muka', 'water_level']),
                    'debit'        => $this->sensorVal($l->params, $latest, ['debit']),

                    // ── AFMR ──────────────────────────────────────────────────────
                    'luas_penampang'   => $this->sensorVal($l->params, $latest, ['luas', 'penampang']),
                    'flow_velocity'    => $this->sensorVal($l->params, $latest, ['flow_velocity', 'velocity', 'kecepatan_aliran']),
                    'elevasi_muka_air' => $this->sensorVal($l->params, $latest, ['elevasi_muka']),
                    'jarak_sensor'     => $this->sensorVal($l->params, $latest, ['jarak_sensor']),
                    'elevasi_sensor'   => $this->sensorVal($l->params, $latest, ['elevasi_sensor', 'tinggi_sensor']),
                    'flowrate'         => $this->sensorVal($l->params, $latest, ['flowrate', 'flow_rate']),
                    'totalizer_1'      => $this->sensorVal($l->params, $latest, ['totalizer_1', 'totalizer1']),
                    'totalizer_2'      => $this->sensorVal($l->params, $latest, ['totalizer_2', 'totalizer2']),
                    'pressure_1'       => $this->sensorVal($l->params, $latest, ['pressure_1', 'pressure1', 'tekanan_1']),
                    'pressure_2'       => $this->sensorVal($l->params, $latest, ['pressure_2', 'pressure2', 'tekanan_2']),
                    'flowmeter_battery'=> $this->sensorVal($l->params, $latest, ['flowmeter_battery']),
                    'fault'            => $this->sensorVal($l->params, $latest, ['fault']),
                    'afmr_units'       => [
                        'flowrate'          => $this->sensorUnit($l->params, ['flowrate', 'flow_rate']),
                        'totalizer_1'       => $this->sensorUnit($l->params, ['totalizer_1', 'totalizer1']),
                        'totalizer_2'       => $this->sensorUnit($l->params, ['totalizer_2', 'totalizer2']),
                        'pressure_1'        => $this->sensorUnit($l->params, ['pressure_1', 'pressure1', 'tekanan_1']),
                        'pressure_2'        => $this->sensorUnit($l->params, ['pressure_2', 'pressure2', 'tekanan_2']),
                        'flowmeter_battery' => $this->sensorUnit($l->params, ['flowmeter_battery']),
                        'fault'             => $this->sensorUnit($l->params, ['fault']),
                    ],

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

        if ($pRain && $col && $this->isArrLogger($logger)) {
            // ARR: classify by running-hour accumulation (consistent with Beranda
            // & notifications). The instantaneous reading is ~always 0 between
            // tips, which would keep every ARR pin stuck on "tidak_hujan".
            $value = ArrRainStatus::hourlyAccumulation((string) ($logger->tabel_main ?? ''), (string) $logger->id_logger, (string) $col);
        } elseif ($latest && $col && isset($latest->{$col})) {
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

    private function isArrLogger($logger): bool
    {
        if ((int) ($logger->id_katlogger ?? 0) === 2) {
            return true;
        }

        return str_contains(strtolower((string) optional($logger->kategori)->nama_kategori), 'arr');
    }

    /**
     * Cari nilai sensor berdasarkan keyword dalam nama_parameter (ambil kemunculan pertama).
     */
    private function buildParameterGroups($logger, $latest): array
    {
        $latestArr = $this->snapshotToArray($latest);

        $parameters = $logger->params
            ->sortBy([
                fn ($a, $b) => ($a->parameterGroup?->sort_order ?? 99) <=> ($b->parameterGroup?->sort_order ?? 99),
                fn ($a, $b) => ($a->id_param ?? 0) <=> ($b->id_param ?? 0),
            ])
            ->map(function ($p) use ($latest, $latestArr) {
                $value = $this->valueFromSnapshot($latest, $latestArr, $p->kolom_sensor);
                $groupCode = strtoupper(trim((string) ($p->parameterGroup?->kode_group ?? 'PENGUKURAN')));

                return [
                    'id_param' => $p->id_param,
                    'nama' => $p->nama_parameter ?: ($p->parameter_utama ?: $p->kolom_sensor),
                    'key' => $this->normalizeSensorKey((string) ($p->parameter_utama ?: $p->nama_parameter ?: $p->kolom_sensor)),
                    'kolom_sensor' => $p->kolom_sensor,
                    'parameter_utama' => $p->parameter_utama,
                    'satuan' => trim((string) $p->satuan),
                    'nilai' => is_numeric($value) ? round((float) $value, 3) : null,
                    'display_value' => $this->displayParameterValue($p, $value),
                    'fault_detail' => (\App\Support\FaultStatus::isFaultParam($p) && is_numeric($value))
                        ? \App\Support\FaultStatus::decode((int) $value)
                        : null,
                    'icon_app' => $p->icon_app,
                    'group_code' => $groupCode,
                    'group_name' => $p->parameterGroup?->nama_group,
                    'is_logger' => $this->isLoggerParameter($p),
                ];
            })
            ->values();

        return [
            'pengukuran' => $parameters
                ->reject(fn ($p) => $p['is_logger'])
                ->values()
                ->all(),
            'logger' => $parameters
                ->filter(fn ($p) => $p['is_logger'])
                ->values()
                ->all(),
        ];
    }

    private function displayParameterValue($param, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        if (\App\Support\FaultStatus::isFaultParam($param) && is_numeric($value)) {
            return \App\Support\FaultStatus::summary((int) $value);
        }

        if (!is_numeric($value)) {
            return (string) $value;
        }

        $formatted = rtrim(rtrim(\App\Support\DisplayFormat::ukur($value, 3), '0'), '.');
        $unit = trim((string) $param->satuan);

        return $formatted . ($unit !== '' ? ' ' . $unit : '');
    }

    private function isLoggerParameter($param): bool
    {
        $key = $this->normalizeSensorKey(
            (string) $param->nama_parameter . ' ' .
            (string) $param->parameter_utama . ' ' .
            (string) $param->kolom_sensor
        );

        if (str_contains($key, 'flowmeter_battery')) {
            return false;
        }

        $groupCode = strtoupper(trim((string) ($param->parameterGroup?->kode_group ?? '')));
        if ($groupCode === 'LOGGER') {
            return true;
        }

        return str_contains($key, 'battery_logger')
            || str_contains($key, 'humidity_logger')
            || str_contains($key, 'temperature_logger');
    }

    private function snapshotToArray($latest): array
    {
        if (!$latest) {
            return [];
        }

        return ($latest instanceof \Illuminate\Database\Eloquent\Model)
            ? $latest->toArray()
            : (array) $latest;
    }

    private function valueFromSnapshot($latest, array $latestArr, ?string $column): mixed
    {
        if (!$latest || !$column) {
            return null;
        }

        if (array_key_exists($column, $latestArr)) {
            return $latestArr[$column];
        }

        if ($latest instanceof \Illuminate\Database\Eloquent\Model) {
            return $latest->getAttribute($column);
        }

        return null;
    }

    private function sensorVal($params, $latest, array $keywords, int $nth = 1): mixed
    {
        if (!$latest) return null;

        // Gunakan toArray() jika Eloquent model (agar accessor ikut), atau (array) jika stdClass dari DB::table
        $latestArr = $this->snapshotToArray($latest);

        $count = 0;
        foreach ($params as $p) {
            if ($this->paramMatchesAnyKeyword($p, $keywords)) {
                $count++;
                if ($count === $nth) {
                    $col = $p->kolom_sensor;
                    if (!$col) break;

                    // Coba ambil nilai: langsung dari array, atau via getAttribute jika Eloquent model
                    $v = $this->valueFromSnapshot($latest, $latestArr, $col);

                    return is_numeric($v) ? round((float) $v, 3) : null;
                }
            }
        }
        return null;
    }

    private function sensorUnit($params, array $keywords): ?string
    {
        foreach ($params as $p) {
            if ($this->paramMatchesAnyKeyword($p, $keywords)) {
                $unit = trim((string) $p->satuan);
                return $unit !== '' ? $unit : null;
            }
        }

        return null;
    }

    private function paramMatchesAnyKeyword($param, array $keywords): bool
    {
        $name = trim((string) $param->nama_parameter);
        $utama = trim((string) $param->parameter_utama);
        $col = trim((string) $param->kolom_sensor);
        $haystack = $this->normalizeSensorKey($name . ' ' . $utama . ' ' . $col);

        foreach ($keywords as $kw) {
            $keyword = $this->normalizeSensorKey((string) $kw);
            if ($keyword !== '' && str_contains($haystack, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeSensorKey(string $value): string
    {
        return strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $value));
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
            $name  = strtolower(trim((string) $param->nama_parameter));
            $utama = strtolower(trim((string) $param->parameter_utama));
            $col   = strtolower(trim((string) $param->kolom_sensor));

            foreach ($aliases as $alias) {
                if (
                    str_contains($name, $alias)  ||
                    str_contains($utama, $alias) ||
                    str_contains($col, $alias)
                ) {
                    return true;
                }
            }
            return false;
        });
    }

    private function resolveLatestSnapshot($logger): mixed
    {
        $latestTemp = collect([$logger->temp16, $logger->temp19, $logger->temp50])
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
