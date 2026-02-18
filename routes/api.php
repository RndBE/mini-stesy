<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataMasukController;

Route::post('/datamasuk/add_awlr2', [DataMasukController::class, 'add_awlr2']);
Route::get('/ping-awlr', fn() => 'pong');
