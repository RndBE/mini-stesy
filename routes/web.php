<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\RealtimeController;
use App\Http\Controllers\AnalisaController;
use Illuminate\Support\Facades\Http;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', function () {
    return redirect()->route('beranda');
});



Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
    Route::get('/peta-lokasi', [PetaController::class, 'index'])->name('peta.lokasi');


    Route::get('/analisa/{id_logger}', [AnalisaController::class, 'index'])->name('analisa.index');
    Route::get('/analisa/data-masuk/{id_logger}', [AnalisaController::class, 'getDataMasuk'])->name('analisa.dataMasuk');
    Route::get('/api/analisa/data/{id_logger}', [AnalisaController::class, 'getChartData'])->name('analisa.data');
    Route::get('/analisa/export/{id_logger}', [AnalisaController::class, 'exportExcel'])->name('analisa.export');

    Route::get('/pengaturan-device', [DeviceController::class, 'index'])->name('device.index');
    Route::get('/data-perangkat', [DeviceController::class, 'dataPerangkat'])->name('device.data'); // New Route
    Route::put('/pengaturan-device/{id}', [DeviceController::class, 'update'])->name('device.update');
    Route::put('/data-perangkat/{id}', [DeviceController::class, 'updateDataPerangkat'])->name('device.updateDataPerangkat');

    Route::get('/realtime-monitoring', [RealtimeController::class, 'index'])->name('realtime.index');
    Route::get('/realtime-monitoring/data/{id}', [RealtimeController::class, 'getData'])->name('realtime.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
