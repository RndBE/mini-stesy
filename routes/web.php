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
    Route::get('/peta-lokasi', [PetaController::class, 'index'])->name('peta.lokasi');
    Route::get('/peta/analisa/{id_logger}', [PetaController::class, 'analisa'])->name('peta.analisa');
    Route::get('/api/peta/data/{id_logger}', [PetaController::class, 'getChartData'])->name('peta.data');
    Route::get('/pengaturan-device', [App\Http\Controllers\DeviceController::class, 'index'])->name('device.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
