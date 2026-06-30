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
use App\Http\Controllers\Api\FcmApiController;

// ─── Existing routes ───────────────────────────────────────────────────────────
Route::post('/datamasuk', [DataMasukController::class, 'datamasuk']);
Route::get('/ping-awlr', fn() => 'pong');

// ─── Mobile API v1 ─────────────────────────────────────────────────────────────
Route::prefix('v1/mobile')->group(function () {

    // ── Auth (public) ──────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/login',  [AuthApiController::class, 'login']);
        Route::get('/config',  [AuthApiController::class, 'appConfig']);
    });

    // ── Protected routes (Sanctum token required & User Status Checked) ─────────
    Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckUserStatus::class])->group(function () {

        // Auth
        Route::post('/auth/logout', [AuthApiController::class, 'logout']);
        Route::get('/auth/me',      [AuthApiController::class, 'me']);
        
        // FCM Token
        Route::post('/fcm/register', [FcmApiController::class, 'register']);

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

        // Chatbot
        Route::post('/chatbot/ask', [\App\Http\Controllers\ChatbotController::class, 'ask']);
        Route::post('/chatbot/stream', [\App\Http\Controllers\ChatbotController::class, 'stream']);
    });
});
