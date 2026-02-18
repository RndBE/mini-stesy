<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\Kategori_Logger;
use App\Models\t_Informasi;
use App\Models\Instansi;
use App\Models\t_Lokasi;
use App\Models\Jiat_data;
use App\Models\Parameter;
use App\Models\List_das;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'jiat'])
            ->orderBy('id_logger')
            ->get()
            ->map(function ($d) {

                return [
                    'id_logger' => $d->id_logger,
                    'nama_logger' => $d->nama_logger,
                    'alamat' => $d->lokasi->alamat ?? '-',
                    'nama_lokasi' => $d->lokasi->nama_lokasi ?? '-',
                    'tabel_main' => $d->tabel_main,
                    // 'sensor_count' => $sensorCount, // 🔥 dikirim ke blade
                    'lokasi' => $d->lokasi,
                    'jiat' => $d->jiat,
                    'params' => $d->params->map(function ($p) {
                        return [
                            'id_param' => $p->id_param,
                            'nama_parameter' => $p->nama_parameter,
                            'kolom_sensor' => $p->kolom_sensor,
                            'satuan' => $p->satuan,
                        ];
                    })->values(),
                    'sensor_count' => $d->sensor_count ?? (str_contains($d->tabel_main, '19') ? 19 : 16),
                ];
            });

        return view('device.index', [
            'title' => 'Pengaturan Device',
            'devices' => $devices,
        ]);
    }

    /**
     * Get data for create device form (Instansi & Logger list)
     */
    public function create()
    {
        $instansis = Instansi::orderBy('nama')->get();

        $loggers = t_Logger::query()
            ->forUser(auth()->user())
            ->select('id_logger', 'nama_logger', 'sensor_count', 'tabel_main')
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($logger) {
                return [
                    'id_logger' => $logger->id_logger,
                    'nama_logger' => $logger->nama_logger,
                    'sensor_count' => $logger->sensor_count ?? (str_contains($logger->tabel_main, '19') ? 19 : 16),
                ];
            });

        return response()->json([
            'instansis' => $instansis,
            'loggers' => $loggers
        ]);
    }

    /**
     * Store new device (create location, update logger, save parameters)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lokasi'       => 'required|string|max:255|unique:t_lokasi,nama_lokasi',
            'alamat'            => 'required|string|max:255',
            // 'pilih_instansi'    => 'required|exists:instansi,id',
            'latitude'          => 'required|numeric',
            'longitude'         => 'required|numeric',
            'sub_kategori'      => 'required|in:jiat,non_jiat',
            'kedalaman_sumur'   => 'nullable|numeric',
            'kedalaman_sensor'  => 'nullable|numeric',
            'kedalaman_pompa'   => 'nullable|numeric',
            'nama_logger'       => 'required|exists:t_logger,id_logger',
            'params'            => 'required|array|min:1',
            'params.*.nama_parameter' => 'required|string|max:255',
            'params.*.kolom_sensor'   => 'required|string|max:50',
            'params.*.satuan'         => 'required|string|max:50',
        ]);

        // dd($validated);

        $defaultDasId = List_das::query()->value('id');
        if (!$defaultDasId) {
            return back()->withErrors([
                'setup' => 'Data DAS belum tersedia. Tambahkan DAS terlebih dahulu.',
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $defaultDasId) {
                // 1. Create location with required columns from schema
                $lokasi = t_Lokasi::create([
                    'nama_lokasi' => $validated['nama_lokasi'],
                    'latitude'    => $validated['latitude'],
                    'longitude'   => $validated['longitude'],
                    'alamat'      => $validated['alamat'],
                    'das_id'      => $defaultDasId,
                ]);

                // 2. Update logger's idlokasi using actual created location id
                $logger = t_Logger::query()
                    ->forUser(auth()->user())
                    ->where('id_logger', $validated['nama_logger'])
                    ->firstOrFail();

                $logger->idlokasi = $lokasi->idlokasi;
                $logger->save();

                // 3. Create/update JIAT data with non-null numeric values
                $kedalamanSumur = $validated['sub_kategori'] === 'jiat'
                    ? (float) ($validated['kedalaman_sumur'] ?? 0)
                    : 0;

                $kedalamanSensor = (float) ($validated['kedalaman_sensor'] ?? 0);
                $kedalamanPompa = (float) ($validated['kedalaman_pompa'] ?? 0);

                Jiat_data::updateOrCreate(
                    ['id_logger' => $logger->id_logger],
                    [
                        'kedalaman_sumur'  => $kedalamanSumur,
                        'kedalaman_sensor' => $kedalamanSensor,
                        'kedalaman_pompa'  => $kedalamanPompa,
                    ]
                );

                // 4. Create parameters
                foreach ($validated['params'] as $param) {
                    Parameter::create([
                        'logger_id'       => $logger->id_logger,
                        'nama_parameter'  => $param['nama_parameter'],
                        'kolom_sensor'    => $param['kolom_sensor'],
                        'satuan'          => $param['satuan'],
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'setup' => 'Setup device gagal disimpan. Cek kembali input setup.',
            ])->withInput();
        }

        return back()->with('success', 'Device berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lokasi'       => 'required|string|max:255',
            'latitude'          => 'nullable|numeric',
            'longitude'         => 'nullable|numeric',
            'kedalaman_sumur'   => 'nullable|numeric',
            'kedalaman_sensor'  => 'nullable|numeric',
            'kedalaman_pompa'   => 'nullable|numeric',
            'params'                    => 'nullable|array',
            'params.*.id_param'         => 'nullable',
            'params.*.nama_parameter'   => 'required_with:params|string|max:255',
            'params.*.kolom_sensor'     => 'required_with:params|string|max:50',
            'params.*.satuan'           => 'required_with:params|string|max:50',
        ]);

        // dd($request->all());

        // Ambil logger + semua relasi yg dibutuhkan
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'jiat', 'params'])
            ->where('id_logger', $id)
            ->firstOrFail();

        if ($logger->lokasi) {
            $logger->lokasi->update([
                'nama_lokasi' => $request->nama_lokasi,
                'latitude'    => $request->latitude,
                'longitude'   => $request->longitude,
            ]);
        }

        if ($logger->jiat) {
            $logger->jiat->update([
                'kedalaman_sumur'  => $request->kedalaman_sumur,
                'kedalaman_sensor' => $request->kedalaman_sensor,
                'kedalaman_pompa'  => $request->kedalaman_pompa,
            ]);
        } else {
            $logger->jiat()->create([
                'kedalaman_sumur'  => $request->kedalaman_sumur,
                'kedalaman_sensor' => $request->kedalaman_sensor,
                'kedalaman_pompa'  => $request->kedalaman_pompa,
            ]);
        }

        if ($request->has('params') && is_array($request->params)) {
            $keptParamIds = [];

            foreach ($request->params as $data) {
                $paramId = $data['id_param'] ?? null;
                $payload = [
                    'nama_parameter' => $data['nama_parameter'] ?? '',
                    'satuan'         => $data['satuan'] ?? '',
                    'kolom_sensor'   => $data['kolom_sensor'] ?? '',
                ];

                if ($paramId) {
                    $param = $logger->params()->where('id_param', $paramId)->first();

                    if ($param) {
                        $param->update($payload);
                        $keptParamIds[] = $param->id_param;
                        continue;
                    }
                }

                $newParam = $logger->params()->create($payload);
                $keptParamIds[] = $newParam->id_param;
            }

            $logger->params()->whereNotIn('id_param', $keptParamIds)->delete();
        }


        return back()->with('success', 'Data device berhasil diperbarui');
    }

    public function dataPerangkat()
    {
        $devices = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'jiat', 'kategori', 'informasi', 'instansi'])
            ->orderBy('id_logger')
            ->get()
            ->map(function ($d) {
                // $sensorCount = 16;
                // if (isset($d->tabel_main)) {
                //     if (str_contains($d->tabel_main, '19')) {
                //         $sensorCount = 19;
                //     } elseif (str_contains($d->tabel_main, '16')) {
                //         $sensorCount = 16;
                //     }
                // }

                return [
                    'id_logger' => $d->id_logger,
                    'nama_logger' => $d->nama_logger,
                    'id_katlogger' => $d->id_katlogger,
                    'kategori' => $d->kategori ? $d->kategori->nama_kategori : '-',
                    'instansi_id' => $d->instansi_id,
                    'instansi' => $d->instansi ? $d->instansi->nama : '-',
                    'seri' => $d->informasi ? $d->informasi->seri_logger : '-',
                    'serial_number' => $d->informasi ? $d->informasi->serial_number : '-',
                    'sensor_type' => $d->informasi ? $d->informasi->sensor : '-',
                    'no_hp' => $d->informasi ? $d->informasi->no_pic : '-',
                    'tanggal_pemasangan' => $d->informasi ? $d->informasi->tanggal_pemasangan : '-',
                    'masa_garansi' => $d->informasi ? $d->informasi->garansi : '-',
                    'nama_penjaga' => $d->informasi ? $d->informasi->nama_pic : '-',
                    'tanggal_pemasangan_raw' => $d->informasi ? $d->informasi->tanggal_pemasangan : null, // Raw Y-m-d for form
                    'masa_garansi_raw' => $d->informasi ? $d->informasi->garansi : null, // Raw Y-m-d for form
                    'jumlah_sensor' => $d->sensor_count,
                    'imei' => $d->informasi ? $d->informasi->imei : null,
                    'awal_kontrak' => $d->informasi ? $d->informasi->awal_kontrak : null,
                ];
            });

        $kategoris = Kategori_Logger::orderBy('nama_kategori')->get();
        $instansis = Instansi::orderBy('nama')->get();

        return view('device.data_perangkat', [
            'title' => 'Data Perangkat',
            'devices' => $devices,
            'kategoris' => $kategoris,
            'instansis' => $instansis,
        ]);
    }

    public function storeDataPerangkat(Request $request)
    {
        $validated = $request->validate([
            'id_logger'          => 'required|string|max:15|unique:t_logger,id_logger',
            'nama_logger'        => 'required|string|max:255',
            'id_katlogger'       => 'nullable|exists:kategori_logger,id_katlogger',
            'instansi_id'        => 'required|exists:instansi,id',
            'seri'               => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            'sensor_type'        => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
            'tanggal_pemasangan' => 'nullable|date',
            'masa_garansi'       => 'nullable|date',
            'nama_penjaga'       => 'nullable|string|max:255',
            'jumlah_sensor'      => 'nullable|integer|in:16,19',
            'elevasi'            => 'nullable|string|max:10',
            'imei'               => 'nullable|string|max:100',
            'awal_kontrak'       => 'nullable|date',
        ]);

        $sensorCount = (int) ($validated['jumlah_sensor'] ?? 16);
        $tabelMain = $sensorCount === 19 ? 't_s19_01' : 't_s16_01';

        $logger = new t_Logger();
        $logger->id_logger = $validated['id_logger'];
        $logger->nama_logger = $validated['nama_logger'];
        $logger->id_katlogger = $validated['id_katlogger'] ?? null;
        $logger->instansi_id = $validated['instansi_id'];
        $logger->sensor_count = $sensorCount;
        $logger->tabel_main = $tabelMain;
        $logger->jeda_notif = 0;
        $logger->save();

        $logger->informasi()->create([
            'logger_id' => $logger->id_logger,
            'seri_logger' => $request->seri,
            'serial_number' => $request->serial_number,
            'sensor' => $request->sensor_type,
            'no_pic' => $request->no_hp,
            'nama_pic' => $request->nama_penjaga,
            'tanggal_pemasangan' => $request->tanggal_pemasangan,
            'garansi' => $request->masa_garansi,
            'elevasi' => $request->elevasi ?? '-',
            'imei'    => $request->imei ?? '-',
            'awal_kontrak'  => $request->awal_kontrak ?? '',
        ]);


        return back()->with('success', 'Data perangkat berhasil ditambahkan.');
    }

    public function updateDataPerangkat(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_logger'        => 'required|string|max:255',
            'id_katlogger'       => 'nullable|exists:kategori_logger,id_katlogger',
            'instansi_id'        => 'required|exists:instansi,id',
            'seri'               => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            'sensor_type'        => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
            'tanggal_pemasangan' => 'nullable|date',
            'masa_garansi'       => 'nullable|date',
            'nama_penjaga'       => 'nullable|string|max:255',
            'elevasi'            => 'nullable|string|max:10',
            'jumlah_sensor'      => 'nullable|integer|in:16,19',
            'imei'               => 'nullable|string|max:100',
            'awal_kontrak'       => 'nullable|date',
        ]);

        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        $sensorCount = (int) ($validated['jumlah_sensor'] ?? $logger->sensor_count ?? 16);
        $tabelMain = $sensorCount === 19 ? 't_s19_01' : 't_s16_01';

        $logger->nama_logger  = $validated['nama_logger'];
        $logger->id_katlogger = $validated['id_katlogger'] ?? null;
        $logger->instansi_id  = $validated['instansi_id'];
        $logger->sensor_count = $sensorCount;
        $logger->tabel_main   = $tabelMain;
        $logger->save();

        $informasi = $logger->informasi;

        if (!$informasi) {
            $informasi = new t_Informasi();
            $informasi->logger_id = $logger->id_logger;
        }

        $informasi->seri_logger        = $validated['seri'] ?? null;
        $informasi->serial_number      = $validated['serial_number'] ?? null;
        $informasi->sensor             = $validated['sensor_type'] ?? null;
        $informasi->no_pic             = $validated['no_hp'] ?? null;
        $informasi->nama_pic           = $validated['nama_penjaga'] ?? null;
        $informasi->tanggal_pemasangan = $validated['tanggal_pemasangan'] ?? null;
        $informasi->garansi            = $validated['masa_garansi'] ?? null;
        $informasi->elevasi            = $validated['elevasi'] ?? ($informasi->elevasi ?? '-');
        $informasi->imei               = $validated['imei'] ?? '-';
        $informasi->awal_kontrak       = $validated['awal_kontrak'] ?? null;
        $informasi->save();

        return back()->with('success', 'Data perangkat berhasil diperbarui.');
    }
}
