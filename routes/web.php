<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\PetaController;
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
    Route::get('/peta-lokasi', [App\Http\Controllers\PetaController::class, 'index'])->name('peta.lokasi');
    Route::get('/peta/analisa/{id_logger}', [PetaController::class, 'analisa'])->name('peta.analisa');
    Route::get('/peta/data-masuk/{id_logger}', [PetaController::class, 'getDataMasuk'])->name('peta.dataMasuk');
    Route::get('/api/peta/data/{id_logger}', [PetaController::class, 'getChartData'])->name('peta.data');
    Route::get('/peta/export/{id_logger}', [PetaController::class, 'exportExcel'])->name('peta.export');
    Route::get('/pengaturan-device', [App\Http\Controllers\DeviceController::class, 'index'])->name('device.index');
    Route::get('/data-perangkat', [App\Http\Controllers\DeviceController::class, 'dataPerangkat'])->name('device.data'); // New Route
    Route::put('/pengaturan-device/{id}', [App\Http\Controllers\DeviceController::class, 'update'])->name('device.update');
    Route::put('/data-perangkat/{id}', [App\Http\Controllers\DeviceController::class, 'updateDataPerangkat'])->name('device.updateDataPerangkat');

    // Realtime Monitoring
    Route::get('/realtime-monitoring', [App\Http\Controllers\RealtimeController::class, 'index'])->name('realtime.index');
    Route::get('/realtime-monitoring/data/{id}', [App\Http\Controllers\RealtimeController::class, 'getData'])->name('realtime.data');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
