<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\RealtimeController;
use App\Http\Controllers\AnalisaController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\ManualBookController;
use App\Http\Controllers\DataMasukController;
use App\Http\Controllers\TingkatSiagaAwlrController;
use App\Http\Controllers\RekapDataController;
use App\Http\Controllers\KategoriLoggerController;
use App\Http\Controllers\ListParameterController;
use App\Http\Controllers\ParameterGroupController;
use App\Http\Controllers\TemplateKategoriParameterController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SettingLoggerController;
use App\Http\Controllers\SkemaIrigasiController;
use App\Http\Controllers\SkemaPipaController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\Api\AwgcCommandController;
use App\Http\Controllers\Api\PumpCommandController;
use Illuminate\Support\Facades\Http;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('beranda');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', 'permission:view_dashboard'])->name('dashboard');

Route::middleware(['auth', 'permission:view_beranda'])->get('/beranda', [BerandaController::class, 'index'])->name('beranda');

Route::middleware(['auth', 'permission:view_peta_lokasi'])->group(function () {
    Route::get('/peta-lokasi', [PetaController::class, 'index'])->name('peta.lokasi');
    Route::get('/peta/analisa/{id_logger}', [PetaController::class, 'analisa'])->name('peta.analisa');
    Route::get('/peta/data-masuk/{id_logger}', [PetaController::class, 'getDataMasuk'])->name('peta.dataMasuk');
    Route::get('/api/peta/data/{id_logger}', [PetaController::class, 'getChartData'])->name('peta.data');
    Route::get('/peta/export/{id_logger}', [PetaController::class, 'exportExcel'])->name('peta.export');

    Route::get('/analisa/{id_logger}', [AnalisaController::class, 'index'])->name('analisa.index');
    Route::get('/analisa/data-masuk/{id_logger}', [AnalisaController::class, 'getDataMasuk'])->name('analisa.dataMasuk');
    Route::get('/api/analisa/data/{id_logger}', [AnalisaController::class, 'getChartData'])->name('analisa.data');
    Route::get('/analisa/export/{id_logger}', [AnalisaController::class, 'exportExcel'])->name('analisa.export');
});

// Rute data-masuk dibuka untuk publik (tanpa login)
Route::get('/data-masuk', [DataMasukController::class, 'index'])->name('data-masuk.index');
Route::get('/api/data-masuk', [DataMasukController::class, 'getData'])->name('data-masuk.api');

Route::middleware(['auth', 'permission:view_peta_lokasi'])->group(function () {
    Route::get('/rekap-data', [RekapDataController::class, 'index'])->name('rekap-data.index');
    Route::get('/api/rekap-data', [RekapDataController::class, 'getData'])->name('rekap-data.api');
    Route::get('/api/rekap-data/missing-minutes', [RekapDataController::class, 'missingMinutes'])->name('rekap-data.missing-minutes');
    Route::post('/api/rekap-data/upload-csv', [RekapDataController::class, 'uploadCsv'])->name('rekap-data.upload-csv');
    Route::get('/skema-irigasi/peta', function() {
        return view('skema.leaflet', ['title' => 'Peta Aliran Leuwigoong']);
    })->name('skema-irigasi.peta');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/skema-irigasi', [SkemaIrigasiController::class, 'index'])->name('skema-irigasi.index');
    Route::get('/skema-irigasi/asbuilt-drawing', [SkemaIrigasiController::class, 'asbuiltDrawing'])->name('skema-irigasi.asbuilt-drawing');
    Route::get('/skema-irigasi/kontrol/{node_id}', [SkemaIrigasiController::class, 'kontrolPintu'])->name('skema-irigasi.kontrol');
    Route::get('/api/skema-irigasi/data', [SkemaIrigasiController::class, 'getData'])->name('skema-irigasi.api');

    // Historis sensor node (untuk chart TMA di panel AWLR)
    Route::get('/api/skema/node/{nodeId}/history', [SkemaIrigasiController::class, 'getNodeHistory'])->name('skema-irigasi.node-history');

    // Skema pipa isometrik SPAM Wosusokas (Plesungan / Mojolaban)
    Route::get('/skema-pipa/{scheme?}', [SkemaPipaController::class, 'index'])->name('skema-pipa');

    // Kelola titik pin skema pipa (dinamis via DB)
    Route::get('/api/skema-pipa/loggers', [SkemaPipaController::class, 'loggersList'])->name('skema-pipa.loggers');
    Route::post('/api/skema-pipa/points', [SkemaPipaController::class, 'storePoint'])->name('skema-pipa.points.store');
    Route::put('/api/skema-pipa/points/{point}', [SkemaPipaController::class, 'updatePoint'])->name('skema-pipa.points.update');
    Route::delete('/api/skema-pipa/points/{point}', [SkemaPipaController::class, 'destroyPoint'])->name('skema-pipa.points.destroy');

    // Perintah kontrol pintu air AWGC
    Route::post('/api/awgc/command', [AwgcCommandController::class, 'store'])->name('awgc.command.store');
    Route::get('/api/awgc/status/{commandId}', [AwgcCommandController::class, 'status'])->name('awgc.command.status');
});

// Endpoint konfirmasi dari firmware alat AWGC (tanpa auth, diamankan via token di payload)
Route::post('/api/awgc/confirm/{commandId}', [AwgcCommandController::class, 'confirm'])->name('awgc.command.confirm');

// Perintah kontrol pompa AWLR JIAT via MQTT
Route::middleware(['auth'])->post('/api/pump/command', [PumpCommandController::class, 'command'])->name('pump.command');

Route::middleware(['auth', 'permission:view_audit_log'])->get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
// Route::middleware(['auth'])->post('/audit-log/client-export', [AuditLogController::class, 'clientExport'])->name('audit-log.client-export');

// Device Routes
Route::middleware(['auth'])->get('/dashboard-setting-logger', [SettingLoggerController::class, 'index'])->name('device.setting-logger');
Route::middleware(['auth', 'permission:view_device'])->get('/pengaturan-device', [DeviceController::class, 'index'])->name('device.index');
Route::middleware(['auth', 'permission:manage_device'])->get('/pengaturan-device/create', [DeviceController::class, 'create'])->name('device.create');
Route::middleware(['auth', 'permission:manage_device'])->post('/pengaturan-device', [DeviceController::class, 'store'])->name('device.store');
Route::middleware(['auth', 'permission:manage_device'])->put('/pengaturan-device/{id}', [DeviceController::class, 'update'])->name('device.update');
Route::middleware(['auth', 'permission:manage_device'])->post('/pengaturan-device/{id}/foto', [DeviceController::class, 'storeFoto'])->name('device.foto.store');
Route::middleware(['auth', 'permission:manage_device'])->delete('/pengaturan-device/foto/{idFoto}', [DeviceController::class, 'destroyFoto'])->name('device.foto.destroy');
Route::middleware(['auth', 'permission:manage_device'])->put('/pengaturan-device/foto/{idFoto}/utama', [DeviceController::class, 'setUtamaFoto'])->name('device.foto.utama');

// Perbaikan Logger Routes (semua user yang login bisa mengubah status perbaikan)
Route::middleware(['auth', 'permission:view_device'])->post('/pengaturan-device/{id}/perbaikan', [DeviceController::class, 'storePerbaikan'])->name('device.perbaikan.store');
Route::middleware(['auth', 'permission:view_device'])->put('/pengaturan-device/{id}/perbaikan/selesai', [DeviceController::class, 'selesaiPerbaikan'])->name('device.perbaikan.selesai');

Route::middleware(['auth', 'permission:view_data_perangkat'])->get('/data-perangkat', [DeviceController::class, 'dataPerangkat'])->name('device.data');
Route::middleware(['auth', 'permission:manage_data_perangkat'])->post('/data-perangkat', [DeviceController::class, 'storeDataPerangkat'])->name('device.storeDataPerangkat');
Route::middleware(['auth', 'permission:manage_data_perangkat'])->put('/data-perangkat/{id}', [DeviceController::class, 'updateDataPerangkat'])->name('device.updateDataPerangkat');

Route::middleware(['auth', 'permission:view_realtime'])->group(function () {
    Route::get('/realtime-monitoring', [RealtimeController::class, 'index'])->name('realtime.index');
    Route::get('/realtime-monitoring/data/{id}', [RealtimeController::class, 'getData'])->name('realtime.data');
    Route::get('/tingkat-siaga-awlr', [TingkatSiagaAwlrController::class, 'index'])->name('tingkat-siaga-awlr.index');
    Route::put('/tingkat-siaga-awlr/{idLogger}', [TingkatSiagaAwlrController::class, 'update'])->name('tingkat-siaga-awlr.update');
});

Route::middleware(['auth'])->resource('instansi', InstansiController::class)->except(['show']);
Route::middleware(['auth', 'permission:manage_instansi'])
    ->resource('kategori-logger', KategoriLoggerController::class)
    ->parameters(['kategori-logger' => 'kategori'])
    ->names('kategori')
    ->except(['show', 'create', 'edit']);
Route::middleware(['auth', 'permission:manage_instansi'])->resource('list-parameter', ListParameterController::class)->except(['show', 'create', 'edit']);
Route::middleware(['auth', 'permission:manage_instansi'])->resource('parameter-group', ParameterGroupController::class)->except(['show', 'create', 'edit']);
Route::middleware(['auth', 'permission:manage_instansi'])->resource('template-kategori-parameter', TemplateKategoriParameterController::class)->except(['show', 'create', 'edit']);

Route::middleware(['auth', 'permission:manage_rbac'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['create', 'edit']);
    Route::resource('permissions', PermissionController::class)->except(['create', 'edit']);
});

Route::middleware(['auth', 'permission:manage_user'])->resource('users', UserController::class)->except(['create', 'edit']);

Route::middleware(['auth'])->group(function () {
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/download', [\App\Http\Controllers\SettingController::class, 'updateDownload'])->name('settings.download.update');
});

// Notifikasi Custom — hanya superadmin
Route::middleware(['auth', 'permission:manage_user'])->group(function () {
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi', [NotifikasiController::class, 'send'])->name('notifikasi.send');
});

Route::middleware(['auth', 'permission:view_profile'])->get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::middleware(['auth', 'permission:manage_profile'])->get('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
Route::middleware(['auth', 'permission:manage_profile'])->patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::middleware(['auth', 'permission:manage_profile'])->delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::middleware(['auth'])->get('/download', [DownloadController::class, 'index'])->name('download.index');
Route::middleware(['auth'])->get('/download/android/apk', [DownloadController::class, 'apk'])->name('download.android.apk');

// Manual Book — halaman baca terbuka untuk semua user yang login,
// pengelolaannya dibatasi permission manage_manual_book.
Route::middleware(['auth', 'permission:manage_manual_book'])->group(function () {
    Route::get('/manual-book/kelola', [ManualBookController::class, 'kelola'])->name('manual-book.kelola');
    Route::post('/manual-book/kelola', [ManualBookController::class, 'store'])->name('manual-book.store');
    Route::put('/manual-book/kelola/{manualBook}', [ManualBookController::class, 'update'])->whereNumber('manualBook')->name('manual-book.update');
    Route::delete('/manual-book/kelola/{manualBook}', [ManualBookController::class, 'destroy'])->whereNumber('manualBook')->name('manual-book.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/manual-book', [ManualBookController::class, 'index'])->name('manual-book.index');
    Route::get('/manual-book/{manualBook}/preview', [ManualBookController::class, 'preview'])->whereNumber('manualBook')->name('manual-book.preview');
    Route::get('/manual-book/{manualBook}/download', [ManualBookController::class, 'download'])->whereNumber('manualBook')->name('manual-book.download');
});

Route::middleware(['auth'])->post('/chatbot/ask', [ChatbotController::class, 'ask'])->name('chatbot.ask');
Route::middleware(['auth'])->post('/chatbot/stream', [ChatbotController::class, 'stream'])->name('chatbot.stream');

require __DIR__ . '/auth.php';
