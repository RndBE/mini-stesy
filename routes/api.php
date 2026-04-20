<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataMasukController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\PetaApiController;
use App\Http\Controllers\Api\RealtimeApiController;
use App\Http\Controllers\Api\DataPerangkatApiController;
use App\Http\Controllers\Api\AnalisaApiController;
use App\Http\Controllers\Api\BerandaApiController;

// ─── Existing routes ───────────────────────────────────────────────────────────
Route::post('/datamasuk', [DataMasukController::class, 'datamasuk']);
Route::get('/ping-awlr', fn() => 'pong');

// ─── Mobile API v1 ─────────────────────────────────────────────────────────────
Route::prefix('v1/mobile')->group(function () {

    // ── Auth (public) ──────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login',  [AuthApiController::class, 'login']);
    });

    // ── Protected routes (Sanctum token required) ──────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
        Route::get('/auth/me',      [AuthApiController::class, 'me']);

        // Beranda
        Route::get('/beranda/info', [BerandaApiController::class, 'info']);

        // Peta Lokasi
        Route::get('/peta/points', [PetaApiController::class, 'points']);

        // Realtime Monitoring
        Route::get('/realtime/devices',      [RealtimeApiController::class, 'devices']);
        Route::get('/realtime/data/{id}',    [RealtimeApiController::class, 'data']);
        Route::get('/realtime/mqtt-config',  [RealtimeApiController::class, 'mqttConfigEndpoint']);

        // Data Perangkat
        Route::get('/data-perangkat',       [DataPerangkatApiController::class, 'index']);
        Route::get('/data-perangkat/{id}',  [DataPerangkatApiController::class, 'show']);
        Route::post('/data-perangkat',      [DataPerangkatApiController::class, 'store']);
        Route::put('/data-perangkat/{id}',  [DataPerangkatApiController::class, 'update']);

        // Analisa Data
        Route::get('/analisa/{id}',         [AnalisaApiController::class, 'index']);
        Route::get('/analisa/{id}/data',    [AnalisaApiController::class, 'data']);
    });
});
