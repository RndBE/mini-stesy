<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\Kategori_Logger;
use App\Models\t_Informasi;
use Carbon\Carbon;

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
                    'sensor_count' => str_contains($d->tabel_main, '19') ? 19 : 16,
                ];
            });

        return view('device.index', [
            'title' => 'Pengaturan Device',
            'devices' => $devices,
        ]);
    }

    public function dataPerangkat()
    {
        $devices = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'jiat', 'kategori', 'informasi'])
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
                    'seri' => $d->informasi ? $d->informasi->seri_logger : '-',
                    'serial_number' => $d->informasi ? $d->informasi->serial_number : '-',
                    'sensor_type' => $d->informasi ? $d->informasi->sensor : '-',
                    'no_hp' => $d->informasi ? $d->informasi->no_pic : '-',
                    'tanggal_pemasangan' => $d->informasi ? $d->informasi->tanggal_pemasangan : '-',
                    'masa_garansi' => $d->informasi ? $d->informasi->garansi : '-',
                    'nama_penjaga' => $d->informasi ? $d->informasi->nama_pic : '-',
                    'tanggal_pemasangan_raw' => $d->informasi ? $d->informasi->tanggal_pemasangan : null, // Raw Y-m-d for form
                    'masa_garansi_raw' => $d->informasi ? $d->informasi->garansi : null, // Raw Y-m-d for form
                ];
            });

        $kategoris = Kategori_Logger::orderBy('nama_kategori')->get();

        return view('device.data_perangkat', [
            'title' => 'Data Perangkat',
            'devices' => $devices,
            'kategoris' => $kategoris,
        ]);
    }

    public function storeDataPerangkat(Request $request)
    {
        // VALIDASI
        $request->validate([
            'nama_logger'        => 'required|string|max:255',
            'id_katlogger'       => 'nullable|exists:kategori_logger,id_katlogger',
            'seri'               => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            'sensor_type'        => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
            'tanggal_pemasangan' => 'nullable|date',
            'masa_garansi'       => 'nullable|date',
            'nama_penjaga'       => 'nullable|string|max:255',
        ]);

        // BUAT DATA LOGGER BARU
        $logger = new t_Logger();
        $logger->nama_logger = $request->nama_logger;
        $logger->id_katlogger = $request->id_katlogger;
        $logger->id_instansi = auth()->user()->id_instansi;
        $logger->save();

        // BUAT DATA INFORMASI PERANGKAT
        $informasi = new t_Informasi();
        $informasi->id_logger = $logger->id_logger;
        $informasi->seri_logger = $request->seri;
        $informasi->serial_number = $request->serial_number;
        $informasi->sensor = $request->sensor_type;
        $informasi->no_pic = $request->no_hp;
        $informasi->nama_pic = $request->nama_penjaga;
        $informasi->tanggal_pemasangan = $request->tanggal_pemasangan;
        $informasi->garansi = $request->masa_garansi;
        $informasi->save();

        // REDIRECT KE HALAMAN SEBELUMNYA
        return back()->with('success', 'Data perangkat berhasil ditambahkan.');
    }

    public function updateDataPerangkat(Request $request, $id)
    {
        // VALIDASI
        $request->validate([
            'nama_logger'        => 'required|string|max:255',
            'id_katlogger'       => 'nullable|exists:kategori_logger,id_katlogger',
            'seri'               => 'nullable|string|max:255',
            'serial_number'      => 'nullable|string|max:255',
            'sensor_type'        => 'nullable|string|max:255',
            'no_hp'              => 'nullable|string|max:20',
            'tanggal_pemasangan' => 'nullable|date',
            'masa_garansi'       => 'nullable|date',
            'nama_penjaga'       => 'nullable|string|max:255',
        ]);

        //CARI LOGGER
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        //UPDATE DATA UTAMA
        $logger->nama_logger   = $request->nama_logger;
        $logger->id_katlogger  = $request->id_katlogger;
        $logger->save();

        //AMBIL / BUAT DATA INFORMASI
        $informasi = $logger->informasi;

        if (!$informasi) {
            $informasi = new t_Informasi();
            $informasi->id_logger = $logger->id_logger;
        }

        // UPDATE INFORMASI PERANGKAT
        $informasi->seri_logger      = $request->seri;
        $informasi->serial_number    = $request->serial_number;
        $informasi->sensor           = $request->sensor_type;
        $informasi->no_pic           = $request->no_hp;
        $informasi->nama_pic         = $request->nama_penjaga;
        $informasi->tanggal_pemasangan = $request->tanggal_pemasangan;
        $informasi->garansi            = $request->masa_garansi;

        $informasi->save();

        //BALIK KE HALAMAN SEBELUMNYA
        return back()->with('success', 'Data perangkat berhasil diperbarui.');
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

        // dd($request->params);
        if ($request->has('params') && is_array($request->params)) {
            foreach ($request->params as $paramId => $data) {

                $param = $logger->params()->where('id_param', $paramId)->first();

                if ($param) {
                    $param->update([
                        'nama_parameter' => $data['nama_parameter'],
                        'satuan'         => $data['satuan'],
                        'kolom_sensor'   => $data['kolom_sensor'],
                    ]);
                }
            }
        }


        return back()->with('success', 'Data device berhasil diperbarui');
    }
}
