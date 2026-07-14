<?php

namespace App\Http\Controllers;

use App\Models\PipaPoint;
use App\Models\t_Logger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SkemaPipaController extends Controller
{
    /**
     * Halaman skema pipa isometrik (SPAM Wosusokas).
     *
     * Artwork isometrik di-export dari Figma sebagai satu file SVG
     * (public/Pipa_<Skema>.svg) lalu ditumpuk dengan pin overlay yang
     * bisa diklik. Koordinat pin disimpan dalam persen (x/y terhadap
     * ukuran artwork) supaya gampang di-tuning tanpa menyentuh SVG.
     */
    public function index(string $scheme = 'plesungan')
    {
        $this->authorizeView();

        $schemes = $this->schemes();

        // Skema tidak dikenal -> jatuhkan ke default agar tidak 404 saat ganti-ganti.
        if (! isset($schemes[$scheme])) {
            $scheme = 'plesungan';
        }

        $active = $schemes[$scheme];

        // Titik pin diambil DINAMIS dari DB (tabel pipa_points), lalu tiap titik
        // yang punya logger_id diisi pembacaan sensor terbaru.
        $active['points'] = $this->attachLiveData($this->loadPoints($scheme));

        return view('skema.pipa', [
            'title'      => 'Skema Pipa',
            'schemeKey'  => $scheme,
            'scheme'     => $active,
            // Hanya admin Wosusokas (atau superadmin) yang boleh kelola titik.
            'canManage'  => (bool) optional(auth()->user())->canManageSkemaPipa(),
            // Daftar ringkas untuk tombol pemilih skema.
            'schemeList' => collect($schemes)->map(fn ($s, $key) => [
                'key'       => $key,
                'name'      => $s['name'],
                'available' => $s['available'],
            ])->values(),
        ]);
    }

    /**
     * Skema pipa hanya untuk instansi SPAM Wosusokas. User dari instansi lain
     * (kecuali superadmin) tidak boleh membuka halaman ini.
     */
    private function authorizeView(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->canViewSkemaPipa(), 403,
            'Skema pipa hanya tersedia untuk instansi SPAM Wosusokas.');
    }

    /**
     * Mengelola titik pin (tambah/ubah/hapus) butuh admin instansi Wosusokas.
     * Ini pengaman utama di sisi server; tombol di UI hanya kosmetik.
     */
    private function authorizeManage(): void
    {
        $user = auth()->user();
        abort_unless($user && $user->canManageSkemaPipa(), 403,
            'Anda tidak berhak mengelola titik skema pipa.');
    }

    /**
     * Mengisi tiap titik (yang punya 'logger_id') dengan pembacaan sensor
     * terbaru dari logger asli. Pemetaan channel diambil dari tabel Parameter
     * (nama_parameter -> kolom_sensor), lalu dibaca dari snapshot terbaru
     * ($logger->temp: temp_s16_latest / s19 / s50 sesuai sensor_count).
     *
     * @param  array<int,array<string,mixed>>  $points
     * @return array<int,array<string,mixed>>
     */
    private function attachLiveData(array $points): array
    {
        $ids = collect($points)->pluck('logger_id')->filter()->unique()->values();
        if ($ids->isEmpty()) {
            return $points;
        }

        $loggers = t_Logger::whereIn('id_logger', $ids)->with('params')->get()->keyBy('id_logger');

        // Ambang "online": data lebih baru dari 30 menit terakhir.
        $onlineThreshold = Carbon::now()->subMinutes(30);

        foreach ($points as &$p) {
            $p['pressure_display'] = $this->normalizePressureDisplay($p['pressure_display'] ?? 'auto');

            $lid = $p['logger_id'] ?? null;
            if (! $lid) {
                continue; // titik manual (mis. reservoir) -> biarkan data config
            }
            if (! isset($loggers[$lid])) {
                // logger_id menunjuk logger yang sudah dihapus/tidak valid ->
                // jangan biarkan default "Online" di UI, tandai tak diketahui.
                $p['status'] = 'unknown';
                continue;
            }

            $logger = $loggers[$lid];
            $p['logger_name'] = $logger->nama_logger;

            $snap = $logger->temp; // snapshot terbaru sesuai family sensor
            if (! $snap) {
                $p['status'] = 'offline';
                continue;
            }

            // Cari kolom_sensor untuk tiap besaran berdasarkan nama parameter.
            $norm = fn ($value) => trim(preg_replace('/\s+/', ' ', str_replace(['_', '-'], ' ', strtolower((string) $value))));
            $colOf = function (array $primaryKeys, array $exactNames, array $containsNames = []) use ($logger, $norm) {
                $primaryKeys = collect($primaryKeys)->map($norm)->all();
                $exactNames = collect($exactNames)->map($norm)->all();
                $containsNames = collect($containsNames)->map($norm)->all();

                $param = $logger->params->first(
                    fn ($pr) => in_array($norm($pr->parameter_utama), $primaryKeys, true)
                );

                $param ??= $logger->params->first(
                    fn ($pr) => in_array($norm($pr->nama_parameter), $exactNames, true)
                );

                $param ??= $logger->params->first(function ($pr) use ($containsNames, $norm) {
                    $name = $norm($pr->nama_parameter);
                    foreach ($containsNames as $needle) {
                        if (str_contains($name, $needle)) {
                            return true;
                        }
                    }
                    return false;
                });

                return $param?->kolom_sensor;
            };
            $sensorValue = fn (?string $column) => ($column && $snap->{$column} !== null)
                ? round((float) $snap->{$column}, 2)
                : null;

            $flowCol      = $colOf(['flowrate'], ['flowrate'], ['flow']);          // Flowrate
            $pressure1Col = $colOf(['pressure_1'], ['pressure 1', 'pressure']);    // Pressure 1 / Pressure
            $pressure2Col = $colOf(['pressure_2'], ['pressure 2']);                // Pressure 2
            $totalCol     = $colOf(['totalizer_1'], ['totalizer 1', 'totalizer'], ['totalizer']); // Totalizer 1

            $flowrate = $sensorValue($flowCol);
            $pressure1 = $sensorValue($pressure1Col);
            $pressure2 = $sensorValue($pressure2Col);
            $totalizer = $sensorValue($totalCol);

            if ($flowrate !== null) $p['flowrate'] = $flowrate;
            if ($pressure1 !== null) {
                $p['pressure_1'] = $pressure1;
                $p['pressure'] = $pressure1;
            }
            $p['pressure_2'] = $pressure2;
            if ($totalizer !== null) $p['totalizer'] = $totalizer;

            $waktu = $snap->waktu ? Carbon::parse($snap->waktu) : null;
            $p['status']     = ($waktu && $waktu->gt($onlineThreshold)) ? 'normal' : 'offline';
            $p['updated_at'] = $waktu?->format('d M Y H:i');
        }
        unset($p);

        return $points;
    }

    /**
     * Ambil titik pin dari DB untuk sebuah skema, dalam bentuk array yang
     * dipakai view. logger_id yang terisi akan diperkaya oleh attachLiveData().
     *
     * @return array<int,array<string,mixed>>
     */
    private function loadPoints(string $scheme): array
    {
        return PipaPoint::where('scheme', $scheme)
            ->orderBy('sort_order')->orderBy('id')
            ->get()
            ->map(fn (PipaPoint $p) => $this->pointToArray($p))
            ->all();
    }

    /** Bentuk 1 titik DB jadi array untuk front-end. */
    private function pointToArray(PipaPoint $p): array
    {
        return [
            'id'        => 'pt' . $p->id,   // id DOM unik
            'point_id'  => $p->id,          // id DB (untuk edit/hapus)
            'logger_id' => $p->logger_id,
            'label'     => $p->label,
            'kind'      => $p->kind,
            'x'         => $p->x,
            'y'         => $p->y,
            'pressure'  => $p->pressure,
            'pressure_1' => $p->pressure,
            'pressure_2' => null,
            'pressure_display' => $this->normalizePressureDisplay($p->pressure_display ?? 'auto'),
            'flowrate'  => $p->flowrate,
            'totalizer' => $p->totalizer,
        ];
    }

    private function normalizePressureDisplay(?string $value): string
    {
        return in_array($value, ['auto', 'pressure_1', 'pressure_2', 'both'], true)
            ? $value
            : 'auto';
    }

    /** Validasi payload titik (store/update). */
    private function validatePoint(Request $request): array
    {
        $data = $request->validate([
            'scheme'    => ['required', 'string', 'max:50'],
            'logger_id' => ['nullable', 'string', 'max:15', 'exists:t_logger,id_logger'],
            'label'     => ['nullable', 'string', 'max:255'],
            'kind'      => ['required', 'in:inlet,outlet,reservoir'],
            'pressure_display' => ['nullable', 'in:auto,pressure_1,pressure_2,both'],
            'x'         => ['required', 'numeric', 'between:0,100'],
            'y'         => ['required', 'numeric', 'between:0,100'],
        ]);

        $data['pressure_display'] = $this->normalizePressureDisplay($data['pressure_display'] ?? 'auto');

        return $data;
    }

    /** Isi label dari nama logger bila label kosong. */
    private function fillLabelFromLogger(array $data): array
    {
        if (empty($data['label']) && ! empty($data['logger_id'])) {
            $data['label'] = optional(
                t_Logger::where('id_logger', $data['logger_id'])->first()
            )->nama_logger;
        }
        return $data;
    }

    /** Tambah titik baru (dipanggil saat klik peta di mode kelola). */
    public function storePoint(Request $request)
    {
        $this->authorizeManage();

        $data = $this->fillLabelFromLogger($this->validatePoint($request));
        $data['sort_order'] = (int) PipaPoint::where('scheme', $data['scheme'])->max('sort_order') + 1;
        $point = PipaPoint::create($data);

        return response()->json(['ok' => true, 'point' => $this->attachLiveData([$this->pointToArray($point)])[0]]);
    }

    /** Ubah titik (posisi / label / jenis / logger). */
    public function updatePoint(Request $request, PipaPoint $point)
    {
        $this->authorizeManage();

        $data = $this->fillLabelFromLogger($this->validatePoint($request));
        $point->update($data);

        return response()->json(['ok' => true, 'point' => $this->attachLiveData([$this->pointToArray($point->fresh())])[0]]);
    }

    /** Hapus titik. */
    public function destroyPoint(PipaPoint $point)
    {
        $this->authorizeManage();

        $point->delete();
        return response()->json(['ok' => true]);
    }

    /**
     * Daftar logger untuk dropdown kelola titik. Difilter ke kategori AFMR
     * (id_katlogger = 3), yaitu flow meter yang relevan untuk skema pipa.
     */
    public function loggersList()
    {
        $this->authorizeManage();

        $loggers = t_Logger::query()
            ->where('id_katlogger', 3) // 3 = AFMR (flow meter)
            ->orderBy('nama_logger')
            ->get(['id_logger', 'nama_logger']);

        return response()->json($loggers);
    }

    /**
     * Definisi semua skema pipa. Untuk menambah skema baru:
     * 1. Export artwork dari Figma -> public/Pipa_<Nama>.svg
     * 2. Tambahkan entri di sini dengan koordinat pin (persen) + data dummy.
     * 3. Set 'available' => true.
     */
    private function schemes(): array
    {
        return [
            'plesungan' => [
                'name'      => 'Plesungan',
                'available' => true,
                // Punya inlet & outlet -> marker dibedakan warnanya (inlet hijau, outlet merah).
                'marker_by_kind' => true,
                'layers' => [
                    'base'   => 'Pipa_Plesungan5.png',
                    'detail' => 'Detail_Pipa_Plesungan_5.png',
                ],
                'detail_offset' => ['x' => 0, 'y' => 0],
                'art_width'  => 3911,
                'art_height' => 2933,
            ],

            'mojolaban' => [
                'name'      => 'Mojolaban',
                'available' => true,
                // Tidak ada konsep inlet/outlet -> semua marker seragam (merah default).
                'marker_by_kind' => false,
                'layers' => [
                    'under'  => 'Pipa_Mojolaban_line.png',     // garis/jalan (paling bawah)
                    'base'   => 'Pipa_Mojolaban.png',          // pipa (tengah)
                    'detail' => 'Detail_Pipa_Mojolaban1.png',  // bangunan (paling depan)
                ],
                'detail_offset' => ['x' => 0, 'y' => 0],
                'art_width'  => 3911,
                'art_height' => 2933,
            ],
        ];
    }
}
