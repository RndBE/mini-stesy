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
use App\Http\Controllers\DataMasukController;
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

Route::middleware(['auth', 'permission:view_peta_lokasi'])->group(function () {
    Route::get('/data-masuk', [DataMasukController::class, 'index'])->name('data-masuk.index');
    Route::get('/api/data-masuk', [DataMasukController::class, 'getData'])->name('data-masuk.api');
});

// Device Routes
Route::middleware(['auth', 'permission:view_device'])->get('/pengaturan-device', [DeviceController::class, 'index'])->name('device.index');
Route::middleware(['auth', 'permission:manage_device'])->get('/pengaturan-device/create', [DeviceController::class, 'create'])->name('device.create');
Route::middleware(['auth', 'permission:manage_device'])->post('/pengaturan-device', [DeviceController::class, 'store'])->name('device.store');
Route::middleware(['auth', 'permission:manage_device'])->put('/pengaturan-device/{id}', [DeviceController::class, 'update'])->name('device.update');

Route::middleware(['auth', 'permission:view_data_perangkat'])->get('/data-perangkat', [DeviceController::class, 'dataPerangkat'])->name('device.data');
Route::middleware(['auth', 'permission:manage_data_perangkat'])->post('/data-perangkat', [DeviceController::class, 'storeDataPerangkat'])->name('device.storeDataPerangkat');
Route::middleware(['auth', 'permission:manage_data_perangkat'])->put('/data-perangkat/{id}', [DeviceController::class, 'updateDataPerangkat'])->name('device.updateDataPerangkat');

Route::middleware(['auth', 'permission:view_realtime'])->group(function () {
    Route::get('/realtime-monitoring', [RealtimeController::class, 'index'])->name('realtime.index');
    Route::get('/realtime-monitoring/data/{id}', [RealtimeController::class, 'getData'])->name('realtime.data');
});

Route::middleware(['auth', 'permission:manage_instansi'])->resource('instansi', InstansiController::class)->except(['show']);

Route::middleware(['auth', 'permission:manage_rbac'])->group(function () {
    Route::resource('roles', RoleController::class)->except(['create', 'edit']);
    Route::resource('permissions', PermissionController::class)->except(['create', 'edit']);
});

Route::middleware(['auth', 'permission:manage_user'])->resource('users', UserController::class)->except(['create', 'edit']);

Route::middleware(['auth', 'permission:view_profile'])->get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::middleware(['auth', 'permission:manage_profile'])->patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::middleware(['auth', 'permission:manage_profile'])->delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

Route::middleware(['auth'])->get('/download', [DownloadController::class, 'index'])->name('download.index');

require __DIR__ . '/auth.php';
