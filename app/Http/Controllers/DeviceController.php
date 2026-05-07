<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\t_Logger;
use App\Models\Kategori_logger;
use App\Models\t_Informasi;
use App\Models\Instansi;
use App\Models\t_Lokasi;
use App\Models\Jiat_data;
use App\Models\NonJiatData;
use App\Models\AfmrContactData;
use App\Models\AfmrNonContactData;
use App\Models\Parameter;
use App\Models\ParameterGroup;
use App\Models\ListParameter;
use App\Models\TemplateKategoriParameter;
use App\Models\List_das;
use App\Models\Perbaikan;
use App\Models\Foto_pos;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;

class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $devices = t_Logger::query()
            ->forUser(auth()->user())
            ->with(['lokasi', 'params', 'jiat', 'nonjiat', 'afmrContact', 'afmrNonContact', 'perbaikan', 'fotos'])
            ->orderBy('id_logger')
            ->get()
            ->map(function ($d) {

                return [
                    'id_logger'          => $d->id_logger,
                    'nama_logger'        => $d->nama_logger,
                    'id_katlogger'       => $d->id_katlogger,
                    'alamat'             => $d->lokasi->alamat ?? '-',
                    'nama_lokasi'        => $d->lokasi->nama_lokasi ?? '-',
                    'tabel_main'         => $d->tabel_main,
                    'lokasi'             => $d->lokasi,
                    'jiat'               => $d->jiat,
                    'nonjiat'            => $d->nonjiat,
                    'afmr_contact'       => $d->afmrContact,
                    'afmr_noncontact'    => $d->afmrNonContact,
                    'fotos'              => $d->fotos->map(function ($f) {
                        return [
                            'id' => $f->id,
                            'url_foto' => asset('storage/' . $f->url_foto),
                            'foto_utama' => (bool)$f->foto_utama,
                        ];
                    })->values(),
                    'params'             => $d->params->map(function ($p) {
                        return [
                            'id_param'           => $p->id_param,
                            'nama_parameter'     => $p->nama_parameter,
                            'kolom_sensor'       => $p->kolom_sensor,
                            'satuan'             => $p->satuan,
                            'parameter_group_id' => $p->parameter_group_id,
                            'parameter_utama'    => $p->parameter_utama,
                            'icon_app'           => $p->icon_app,
                        ];
                    })->values(),
                    'sensor_count'       => $d->sensor_count ?? (str_contains($d->tabel_main, '19') ? 19 : 16),
                    'status_perbaikan'   => $d->status_perbaikan ?? 'normal',
                    'perbaikan_history'  => $d->perbaikan
                        ->take(5)
                        ->map(fn($p) => [
                            'id'                => $p->id_perbaikan,
                            'keterangan'        => $p->keterangan,
                            'tanggal_perbaikan' => $p->tanggal_perbaikan?->format('d M Y') ?? '-',
                            'petugas'           => $p->petugas,
                            'status_akhir'      => $p->status_akhir,
                            'created_by'        => $p->created_by ?? '-',
                            'created_at'        => optional($p->created_at)->format('d M Y, H:i') ?? '-',
                        ])->values(),
                ];
            });

        $templateMap = [];
        if (Schema::hasTable('template_kategori_parameter') && Schema::hasTable('list_parameter')) {
            $templateMap = TemplateKategoriParameter::query()
                ->with(['listParameter', 'parameterGroup'])
                ->orderBy('id_katlogger')
                ->orderBy('urutan')
                ->get()
                ->groupBy('id_katlogger')
                ->map(function ($rows) {
                    return $rows->map(function ($row) {
                        $list = $row->listParameter;
                        return [
                            'list_parameter_id' => $row->list_parameter_id,
                            'nama_parameter' => $list?->nama_parameter,
                            'parameter_utama' => $list?->parameter_utama,
                            'icon_app' => $list?->icon_app,
                            'kolom_sensor_default' => $row->kolom_sensor_default ?: ($list?->default_kolom_sensor ?? null),
                            'satuan' => $row->satuan_override ?: ($list?->default_satuan ?? null),
                            'parameter_group_id' => $row->parameter_group_id ?: ($list?->default_parameter_group_id ?? null),
                            'urutan' => $row->urutan,
                        ];
                    })->values();
                })
                ->toArray();
        }

        $listParameters = [];
        if (Schema::hasTable('list_parameter')) {
            $listParameters = ListParameter::query()
                ->where('is_active', true)
                ->orderBy('nama_parameter')
                ->get([
                    'id',
                    'nama_parameter',
                    'parameter_utama',
                    'default_satuan',
                    'default_kolom_sensor',
                    'icon_app',
                    'default_parameter_group_id',
                ])
                ->toArray();
        }

        return view('device.index', [
            'title' => 'Pengaturan Device',
            'devices' => $devices,
            'templateMap' => $templateMap,
            'listParameters' => $listParameters,
            'awlrCategoryIds' => $this->awlrCategoryIds(),
            'afmrCategoryIds' => $this->afmrCategoryIds(),
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
            ->select('id_logger', 'nama_logger', 'id_katlogger', 'sensor_count', 'tabel_main')
            ->orderBy('nama_logger')
            ->get()
            ->map(function ($logger) {
                return [
                    'id_logger' => $logger->id_logger,
                    'nama_logger' => $logger->nama_logger,
                    'id_katlogger' => $logger->id_katlogger,
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
            'sub_kategori'      => 'nullable|in:jiat,non_jiat,contact,non_contact',
            'has_pump'          => 'nullable|boolean',
            'kedalaman_sumur'   => 'nullable|numeric',
            'kedalaman_sensor'  => 'nullable|numeric',
            'kedalaman_pompa'   => 'nullable|numeric',
            'jarak_sensor_ke_air' => 'nullable|numeric',
            'tinggi_sensor'     => 'nullable|numeric',
            'elevasi_max'       => 'nullable|numeric',
            'elevasi_min'       => 'nullable|numeric',
            'nama_logger'       => 'required|exists:t_logger,id_logger',
            'params'            => 'required|array|min:1',
            'params.*.nama_parameter' => 'required|string|max:255',
            'params.*.kolom_sensor'   => 'required|string|max:50',
            'params.*.satuan'         => 'required|string|max:50',
            'params.*.parameter_group_id' => 'nullable|integer|exists:parameter_groups,id',
            'params.*.parameter_utama' => 'nullable|string|max:50',
            'params.*.icon_app' => 'nullable|string|max:255',
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

                // 3. JIAT data hanya dipakai untuk kategori AWLR
                if ($this->isAwlrCategoryId($logger->id_katlogger)) {
                    $subKategori = $validated['sub_kategori'] ?? 'non_jiat';

                    if ($subKategori === 'jiat') {
                        Jiat_data::updateOrCreate(
                            ['id_logger' => $logger->id_logger],
                            [
                                'kedalaman_sumur'  => (float) ($validated['kedalaman_sumur'] ?? 0),
                                'kedalaman_sensor' => (float) ($validated['kedalaman_sensor'] ?? 0),
                                'kedalaman_pompa'  => (float) ($validated['kedalaman_pompa'] ?? 0),
                                'has_pump'         => (bool) ($validated['has_pump'] ?? false),
                            ]
                        );
                        NonJiatData::where('id_logger', $logger->id_logger)->delete();
                    } elseif ($subKategori === 'non_jiat') {
                        NonJiatData::updateOrCreate(
                            ['id_logger' => $logger->id_logger],
                            [
                                'jarak_sensor_ke_air' => (float) ($validated['jarak_sensor_ke_air'] ?? 0),
                                'tinggi_sensor'       => (float) ($validated['tinggi_sensor'] ?? 0),
                                'elevasi_max'         => isset($validated['elevasi_max']) ? (float) $validated['elevasi_max'] : null,
                                'elevasi_min'         => isset($validated['elevasi_min']) ? (float) $validated['elevasi_min'] : null,
                            ]
                        );
                        Jiat_data::where('id_logger', $logger->id_logger)->delete();
                    }
                }

                // 3b. AFMR data: contact atau non-contact
                if ($this->isAfmrCategoryId($logger->id_katlogger)) {
                    $subKategori = $validated['sub_kategori'] ?? 'non_contact';

                    if ($subKategori === 'contact') {
                        AfmrContactData::updateOrCreate(
                            ['id_logger' => $logger->id_logger],
                            [
                                'lebar_sungai'    => isset($validated['lebar_sungai']) ? (float) $validated['lebar_sungai'] : null,
                                'kedalaman_rata'  => isset($validated['kedalaman_rata']) ? (float) $validated['kedalaman_rata'] : null,
                                'koefisien_debit' => isset($validated['koefisien_debit']) ? (float) $validated['koefisien_debit'] : null,
                                'catatan'         => $validated['catatan_afmr'] ?? null,
                            ]
                        );
                        AfmrNonContactData::where('id_logger', $logger->id_logger)->delete();
                    } else {
                        AfmrNonContactData::updateOrCreate(
                            ['id_logger' => $logger->id_logger],
                            [
                                'tinggi_sensor'       => isset($validated['tinggi_sensor']) ? (float) $validated['tinggi_sensor'] : null,
                                'jarak_sensor_ke_air' => isset($validated['jarak_sensor_ke_air']) ? (float) $validated['jarak_sensor_ke_air'] : null,
                                'elevasi_max'         => isset($validated['elevasi_max']) ? (float) $validated['elevasi_max'] : null,
                                'elevasi_min'         => isset($validated['elevasi_min']) ? (float) $validated['elevasi_min'] : null,
                                'catatan'             => $validated['catatan_afmr'] ?? null,
                            ]
                        );
                        AfmrContactData::where('id_logger', $logger->id_logger)->delete();
                    }
                }

                // 4. Create parameters
                foreach ($validated['params'] as $param) {
                    $parameterUtama = $this->resolveParameterUtama(
                        $param['parameter_utama'] ?? null,
                        $param['nama_parameter'] ?? null
                    );
                    $parameterGroupId = $param['parameter_group_id']
                        ?? $this->inferParameterGroupId($param['nama_parameter'] ?? null, $parameterUtama);

                    Parameter::create([
                        'logger_id'       => $logger->id_logger,
                        'nama_parameter'  => $param['nama_parameter'],
                        'kolom_sensor'    => $param['kolom_sensor'],
                        'satuan'          => $param['satuan'],
                        'parameter_group_id' => $parameterGroupId,
                        'parameter_utama' => $parameterUtama,
                        'icon_app' => $this->normalizeIconPath($param['icon_app'] ?? null),
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
            'sub_kategori'      => 'nullable|in:jiat,non_jiat,contact,non_contact',
            'has_pump'          => 'nullable|boolean',
            'kedalaman_sumur'   => 'nullable|numeric',
            'kedalaman_sensor'  => 'nullable|numeric',
            'kedalaman_pompa'   => 'nullable|numeric',
            'jarak_sensor_ke_air' => 'nullable|numeric',
            'tinggi_sensor'     => 'nullable|numeric',
            'elevasi_max'       => 'nullable|numeric',
            'elevasi_min'       => 'nullable|numeric',
            'params'                    => 'nullable|array',
            'params.*.id_param'         => 'nullable',
            'params.*.nama_parameter'   => 'required_with:params|string|max:255',
            'params.*.kolom_sensor'     => 'required_with:params|string|max:50',
            'params.*.satuan'           => 'required_with:params|string|max:50',
            'params.*.parameter_group_id'  => 'nullable|integer|exists:parameter_groups,id',
            'params.*.parameter_utama'  => 'nullable|string|max:50',
            'params.*.icon_app'  => 'nullable|string|max:255',
        ]);

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

        if ($this->isAwlrCategoryId($logger->id_katlogger)) {
            $subKategori = $request->input('sub_kategori', 'non_jiat');

            if ($subKategori === 'jiat') {
                Jiat_data::updateOrCreate(
                    ['id_logger' => $logger->id_logger],
                    [
                        'kedalaman_sumur'  => (float) ($request->kedalaman_sumur ?? 0),
                        'kedalaman_sensor' => (float) ($request->kedalaman_sensor ?? 0),
                        'kedalaman_pompa'  => (float) ($request->kedalaman_pompa ?? 0),
                        'has_pump'         => (bool) $request->boolean('has_pump'),
                    ]
                );
                NonJiatData::where('id_logger', $logger->id_logger)->delete();
            } elseif ($subKategori === 'non_jiat') {
                NonJiatData::updateOrCreate(
                    ['id_logger' => $logger->id_logger],
                    [
                        'jarak_sensor_ke_air' => (float) ($request->jarak_sensor_ke_air ?? 0),
                        'tinggi_sensor'       => (float) ($request->tinggi_sensor ?? 0),
                        'elevasi_max'         => $request->elevasi_max !== null ? (float) $request->elevasi_max : null,
                        'elevasi_min'         => $request->elevasi_min !== null ? (float) $request->elevasi_min : null,
                    ]
                );
                Jiat_data::where('id_logger', $logger->id_logger)->delete();
            }
        }

        // AFMR: contact atau non-contact
        if ($this->isAfmrCategoryId($logger->id_katlogger)) {
            $subKategori = $request->input('sub_kategori', 'non_contact');

            if ($subKategori === 'contact') {
                AfmrContactData::updateOrCreate(
                    ['id_logger' => $logger->id_logger],
                    [
                        'lebar_sungai'    => $request->lebar_sungai !== null ? (float) $request->lebar_sungai : null,
                        'kedalaman_rata'  => $request->kedalaman_rata !== null ? (float) $request->kedalaman_rata : null,
                        'koefisien_debit' => $request->koefisien_debit !== null ? (float) $request->koefisien_debit : null,
                        'catatan'         => $request->catatan_afmr ?? null,
                    ]
                );
                AfmrNonContactData::where('id_logger', $logger->id_logger)->delete();
            } else {
                AfmrNonContactData::updateOrCreate(
                    ['id_logger' => $logger->id_logger],
                    [
                        'tinggi_sensor'       => $request->tinggi_sensor !== null ? (float) $request->tinggi_sensor : null,
                        'jarak_sensor_ke_air' => $request->jarak_sensor_ke_air !== null ? (float) $request->jarak_sensor_ke_air : null,
                        'elevasi_max'         => $request->elevasi_max !== null ? (float) $request->elevasi_max : null,
                        'elevasi_min'         => $request->elevasi_min !== null ? (float) $request->elevasi_min : null,
                        'catatan'             => $request->catatan_afmr ?? null,
                    ]
                );
                AfmrContactData::where('id_logger', $logger->id_logger)->delete();
            }
        }

        $normalizeParamName = function ($v) {
            $v = trim((string) $v);
            $v = preg_replace('/\s+/', ' ', $v);
            return $v ?: '';
        };

        if ($request->has('params') && is_array($request->params)) {
            $keptParamIds = [];

            foreach ($request->params as $data) {
                $paramId = $data['id_param'] ?? null;

                $namaParam = $normalizeParamName($data['nama_parameter'] ?? '');
                $kolomSensor = trim((string) ($data['kolom_sensor'] ?? ''));
                $satuan = trim((string) ($data['satuan'] ?? ''));
                $parameterUtama = $this->resolveParameterUtama(
                    $data['parameter_utama'] ?? null,
                    $namaParam ?: null
                );

                $payload = [
                    'nama_parameter' => $namaParam,
                    'satuan'         => $satuan,
                    'kolom_sensor'   => $kolomSensor,
                    'parameter_group_id' => $data['parameter_group_id']
                        ?? $this->inferParameterGroupId($namaParam ?: null, $parameterUtama),
                    'parameter_utama' => $parameterUtama,
                    'icon_app' => $this->normalizeIconPath($data['icon_app'] ?? null),
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

    /**
     * Simpan catatan perbaikan dan set status logger menjadi 'perbaikan'
     */
    public function storePerbaikan(Request $request, $id)
    {
        $request->validate([
            'keterangan'        => 'required|string|max:2000',
            'tanggal_perbaikan' => 'required|date',
            'petugas'           => 'required|string|max:255',
        ]);

        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        // Simpan catatan perbaikan
        Perbaikan::create([
            'id_logger'         => $logger->id_logger,
            'keterangan'        => $request->keterangan,
            'tanggal_perbaikan' => $request->tanggal_perbaikan,
            'petugas'           => $request->petugas,
            'status_akhir'      => 'sedang_perbaikan',
            'created_by'        => auth()->user()->nama ?? auth()->user()->username ?? '-',
        ]);

        // Update status logger
        $logger->status_perbaikan = 'perbaikan';
        $logger->save();

        // Ambil history terbaru untuk dikembalikan ke frontend
        $history = Perbaikan::where('id_logger', $id)
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id'                => $p->id_perbaikan,
                'keterangan'        => $p->keterangan,
                'tanggal_perbaikan' => $p->tanggal_perbaikan?->format('d M Y') ?? '-',
                'petugas'           => $p->petugas,
                'status_akhir'      => $p->status_akhir,
                'created_by'        => $p->created_by ?? '-',
                'created_at'        => optional($p->created_at)->format('d M Y, H:i') ?? '-',
            ])->values();

        return response()->json([
            'success'         => true,
            'message'         => 'Catatan perbaikan berhasil disimpan.',
            'status_perbaikan'=> 'perbaikan',
            'history'         => $history,
        ]);
    }

    /**
     * Set status logger kembali ke normal
     */
    public function selesaiPerbaikan(Request $request, $id)
    {
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        // Update semua catatan yang sedang_perbaikan menjadi selesai
        Perbaikan::where('id_logger', $id)
            ->where('status_akhir', 'sedang_perbaikan')
            ->update(['status_akhir' => 'selesai']);

        $logger->status_perbaikan = 'normal';
        $logger->save();

        return response()->json([
            'success'          => true,
            'message'          => 'Status logger kembali ke normal.',
            'status_perbaikan' => 'normal',
        ]);
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

        $kategoris = Kategori_logger::orderBy('nama_kategori')->get();
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

        // Important: allocate/create shard table outside transaction.
        // MySQL DDL (CREATE TABLE) can trigger implicit commit inside transaction.
        $tabelMain = $this->allocateMainTableForSensorCount($sensorCount);

        DB::transaction(function () use ($validated, $sensorCount, $request, $tabelMain) {
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
        });


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
        $currentTable = (string) ($logger->tabel_main ?? '');
        $sameFamily = ($sensorCount >= 19 && str_starts_with($currentTable, 't_s19_'))
            || ($sensorCount < 19 && str_starts_with($currentTable, 't_s16_'));
        $preferredTable = $sameFamily ? $currentTable : null;
        $tabelMain = $this->allocateMainTableForSensorCount($sensorCount, $logger->id_logger, $preferredTable);

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

    private function allocateMainTableForSensorCount(int $sensorCount, ?string $ignoreLoggerId = null, ?string $preferredTable = null): string
    {
        $normalizedSensorCount = $sensorCount >= 19 ? 19 : 16;
        $prefix = $normalizedSensorCount === 19 ? 't_s19_' : 't_s16_';
        $maxLoggerPerTable = max((int) env('MAX_LOGGER_PER_TABLE', 5), 1);

        $query = DB::table('t_logger')
            ->select('tabel_main', DB::raw('COUNT(*) as total'))
            ->whereNotNull('tabel_main')
            ->where('tabel_main', 'like', $prefix . '%')
            ->where('sensor_count', $normalizedSensorCount)
            ->groupBy('tabel_main');

        if (DB::transactionLevel() > 0) {
            $query->lockForUpdate();
        }

        if ($ignoreLoggerId) {
            $query->where('id_logger', '!=', $ignoreLoggerId);
        }

        $countsByTable = $query
            ->get()
            ->mapWithKeys(function ($row) {
                return [(string) $row->tabel_main => (int) $row->total];
            })
            ->all();

        $candidateTables = array_keys($countsByTable);

        if ($preferredTable && str_starts_with($preferredTable, $prefix)) {
            $candidateTables[] = $preferredTable;
        }

        $defaultTable = $prefix . '01';
        $candidateTables[] = $defaultTable;

        $candidateTables = array_values(array_unique($candidateTables));
        usort($candidateTables, function ($a, $b) use ($prefix) {
            return $this->extractShardSuffix($a, $prefix) <=> $this->extractShardSuffix($b, $prefix);
        });

        foreach ($candidateTables as $tableName) {
            if (!str_starts_with($tableName, $prefix)) {
                continue;
            }

            $currentCount = (int) ($countsByTable[$tableName] ?? 0);
            if ($currentCount >= $maxLoggerPerTable) {
                continue;
            }

            $this->ensureTimeseriesTableExists($tableName, $normalizedSensorCount, $maxLoggerPerTable);
            return $tableName;
        }

        $maxSuffix = 1;
        foreach ($candidateTables as $tableName) {
            $suffix = $this->extractShardSuffix($tableName, $prefix);
            if ($suffix > $maxSuffix) {
                $maxSuffix = $suffix;
            }
        }

        $newTable = $prefix . sprintf('%02d', $maxSuffix + 1);
        $this->ensureTimeseriesTableExists($newTable, $normalizedSensorCount, $maxLoggerPerTable);

        return $newTable;
    }

    private function extractShardSuffix(string $tableName, string $prefix): int
    {
        if (!str_starts_with($tableName, $prefix)) {
            return 0;
        }

        $suffix = substr($tableName, strlen($prefix));
        return ctype_digit($suffix) ? (int) $suffix : 0;
    }

    private function ensureTimeseriesTableExists(string $tableName, int $sensorCount, int $maxLoggerPerTable): void
    {
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($sensorCount) {
                $table->charset = 'utf8';
                $table->collation = 'utf8_general_ci';

                $table->bigIncrements('id');
                $table->string('id_logger', 15);
                $table->dateTime('waktu');

                $maxSensor = $sensorCount >= 19 ? 19 : 16;
                for ($i = 1; $i <= $maxSensor; $i++) {
                    $table->float('sensor' . $i)->nullable();
                }

                $table->index(['id_logger', 'waktu']);
                $table->index('waktu');
                $table->foreign('id_logger')->references('id_logger')->on('t_logger')->cascadeOnUpdate()->restrictOnDelete();
            });
        }

        if (Schema::hasTable('ts_table_pool')) {
            DB::table('ts_table_pool')->updateOrInsert(
                ['table_name' => $tableName],
                [
                    'sensor_count' => $sensorCount,
                    'max_logger' => $maxLoggerPerTable,
                    'is_active' => 1,
                    'created_at' => now(),
                ]
            );
        }
    }

    private function inferParameterGroupId(?string $namaParameter, ?string $parameterUtama): ?int
    {
        $groups = $this->parameterGroupMap();
        $nama = strtolower(trim((string) $namaParameter));
        $utama = strtolower(trim((string) $parameterUtama));

        if (
            str_contains($nama, 'angin') || str_contains($utama, 'angin')
            || str_contains($nama, 'wind') || str_contains($utama, 'wind')
        ) {
            return $groups['ANGIN'] ?? null;
        }

        if (
            str_contains($nama, 'battery') || str_contains($utama, 'battery')
            || str_contains($nama, 'humidity') || str_contains($utama, 'humidity')
            || str_contains($nama, 'temperature') || str_contains($utama, 'temperature')
        ) {
            return $groups['LOGGER'] ?? null;
        }

        return $groups['SUMUR'] ?? null;
    }

    private function resolveParameterUtama(?string $parameterUtama, ?string $namaParameter): ?string
    {
        return $this->normalizeParameterKey($parameterUtama);
    }

    private function normalizeParameterKey(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\s+/', '_', $value);
        $value = preg_replace('/_+/', '_', $value);

        return substr(strtolower($value), 0, 50);
    }

    private function normalizeIconPath(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return ltrim($value, '/');
    }

    private function parameterGroupMap(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = ParameterGroup::query()
                ->pluck('id', 'kode_group')
                ->map(fn($id) => (int) $id)
                ->all();
        }

        return $cache;
    }

    private function isAwlrCategoryId($idKatlogger): bool
    {
        if ($idKatlogger === null || $idKatlogger === '') {
            return false;
        }

        return in_array((int) $idKatlogger, $this->awlrCategoryIds(), true);
    }

    private function awlrCategoryIds(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Kategori_logger::query()
                ->whereRaw('UPPER(nama_kategori) = ?', ['AWLR'])
                ->pluck('id_katlogger')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
        }

        return $cache;
    }

    private function isAfmrCategoryId($idKatlogger): bool
    {
        if ($idKatlogger === null || $idKatlogger === '') {
            return false;
        }

        return in_array((int) $idKatlogger, $this->afmrCategoryIds(), true);
    }

    private function afmrCategoryIds(): array
    {
        static $cache = null;

        if ($cache === null) {
            $cache = Kategori_logger::query()
                ->whereRaw('UPPER(nama_kategori) = ?', ['AFMR'])
                ->pluck('id_katlogger')
                ->map(fn($id) => (int) $id)
                ->values()
                ->all();
        }

        return $cache;
    }
    public function storeFoto(Request $request, $id)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg|max:5120', // max 5MB
        ]);

        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $id)
            ->firstOrFail();

        $path = $request->file('foto')->store('foto_pos', 'public');

        $isFirst = $logger->fotos()->count() === 0;

        $foto = Foto_pos::create([
            'id_logger' => $logger->id_logger,
            'url_foto' => $path,
            'foto_utama' => $isFirst ? 1 : 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto berhasil diupload',
            'data' => [
                'id' => $foto->id,
                'url_foto' => asset('storage/' . $foto->url_foto),
                'foto_utama' => (bool)$foto->foto_utama,
            ]
        ]);
    }

    public function destroyFoto($idFoto)
    {
        $foto = Foto_pos::findOrFail($idFoto);
        
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $foto->id_logger)
            ->firstOrFail();

        if (Storage::disk('public')->exists($foto->url_foto)) {
            Storage::disk('public')->delete($foto->url_foto);
        }

        $foto->delete();

        // If the deleted photo was main, set the first available as main
        if ($foto->foto_utama) {
            $nextFoto = Foto_pos::where('id_logger', $logger->id_logger)->first();
            if ($nextFoto) {
                $nextFoto->update(['foto_utama' => 1]);
            }
        }

        return response()->json(['success' => true, 'message' => 'Foto berhasil dihapus']);
    }

    public function setUtamaFoto(Request $request, $idFoto)
    {
        $foto = Foto_pos::findOrFail($idFoto);
        
        $logger = t_Logger::query()
            ->forUser(auth()->user())
            ->where('id_logger', $foto->id_logger)
            ->firstOrFail();

        // Reset all
        Foto_pos::where('id_logger', $logger->id_logger)->update(['foto_utama' => 0]);

        // Set main
        $foto->update(['foto_utama' => 1]);

        return response()->json(['success' => true, 'message' => 'Foto utama berhasil diatur']);
    }
}
