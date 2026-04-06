<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingLoggerController extends Controller
{
    public function index()
    {
        return view('device.setting-logger');
    }
}
