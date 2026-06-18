<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class DownloadController extends Controller
{
    /**
     * Display the download application page.
     * Nilai diambil dari pengaturan admin (app_settings.json).
     */
    public function index()
    {
        $settings = SettingController::getSettings();

        $androidMode = $settings['download_android_mode'] ?? 'apk';
        $apkPath = $settings['download_android_apk_path'] ?? null;

        if ($androidMode === 'playstore') {
            $androidUrl = $settings['download_android_playstore_url'] ?? '';
        } else {
            $androidUrl = ($apkPath && Storage::disk('local')->exists($apkPath))
                ? route('download.android.apk')
                : '';
        }

        $iosUrl = $settings['download_ios_url'] ?? '';

        $downloads = [
            'android' => [
                'name' => 'Aplikasi Android',
                'mode' => $androidMode,
                'url' => $androidUrl,
                'available' => $androidUrl !== '',
                'icon' => 'android',
                'version' => $settings['download_android_version'] ?: '-',
                'apk_name' => $settings['download_android_apk_name'] ?? null,
                'apk_size' => $settings['download_android_apk_size'] ?? null,
            ],
            'ios' => [
                'name' => 'Aplikasi iOS',
                'url' => $iosUrl,
                'available' => $iosUrl !== '',
                'icon' => 'apple',
                'version' => $settings['download_ios_version'] ?: '-',
            ],
        ];

        return view('download.index', [
            'title' => 'Unduh Aplikasi',
            'downloads' => $downloads,
        ]);
    }

    /**
     * Stream APK yang di-upload admin sebagai unduhan.
     */
    public function apk()
    {
        $settings = SettingController::getSettings();
        $path = $settings['download_android_apk_path'] ?? null;

        if (!$path || !Storage::disk('local')->exists($path)) {
            abort(404, 'APK belum tersedia.');
        }

        $downloadName = $settings['download_android_apk_name'] ?: 'aplikasi.apk';

        return Storage::disk('local')->download($path, $downloadName, [
            'Content-Type' => 'application/vnd.android.package-archive',
        ]);
    }
}
