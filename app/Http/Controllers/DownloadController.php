<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadController extends Controller
{
    /**
     * Display the download application page
     */
    public function index()
    {
        // Download URLs for Android and iOS apps
        $downloads = [
            'android' => [
                'name' => 'Aplikasi Android',
                'url' => 'https://mini-stesy.beacontelemetry.com/unduh/mini_stesy_1.2.0.apk',
                'icon' => 'android',
                'version' => '1.2.0',
            ],
            'ios' => [
                'name' => 'Aplikasi iOS',
                'url' => 'https://apps.apple.com/id/app/mini_stesy/id6480156441',
                'icon' => 'apple',
                'version' => '1.3.6',
            ],
        ];

        return view('download.index', [
            'title' => 'Unduh Aplikasi',
            'downloads' => $downloads,
        ]);
    }
}
