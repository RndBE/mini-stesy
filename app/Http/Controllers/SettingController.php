<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\FcmService;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    private $settingsFile = 'app_settings.json';

    public function index()
    {
        // Hanya bisa diakses oleh Superadmin
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_rbac')) {
            abort(403, 'Unauthorized action.');
        }

        $settings = $this->getSettings();

        return view('settings.index', [
            'title' => 'Pengaturan Sistem',
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_rbac')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'maintenance_mode' => 'nullable|boolean',
            'maintenance_message' => 'nullable|string',
            'latest_app_version' => 'nullable|string',
            'force_update' => 'nullable|boolean',
            'update_url' => 'nullable|string'
        ]);

        $settings = $this->getSettings();
        
        $oldMaintenanceMode = $settings['maintenance_mode'] ?? false;
        $oldLatestVersion = $settings['latest_app_version'] ?? '1.0.0';
        $newMaintenanceMode = $request->has('maintenance_mode') ? true : false;
        $maintenanceMessage = $request->input('maintenance_message', 'Server sedang dalam perbaikan.');

        $settings['maintenance_mode'] = $newMaintenanceMode;
        $settings['maintenance_message'] = $maintenanceMessage;
        
        $newLatestVersion = $request->input('latest_app_version', '1.0.0');
        $settings['latest_app_version'] = $newLatestVersion;
        $settings['force_update'] = $request->has('force_update') ? true : false;
        $settings['update_url'] = $request->input('update_url', '');

        Storage::disk('local')->put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        // Kirim notifikasi massal berdasarkan perubahan status
        if ($newMaintenanceMode && !$oldMaintenanceMode) {
            $this->broadcastMaintenanceNotification("Informasi Pemeliharaan Server", $maintenanceMessage);
        } elseif (!$newMaintenanceMode && $oldMaintenanceMode) {
            $this->broadcastMaintenanceNotification("Pemeliharaan Selesai", "Server telah kembali beroperasi normal. Terima kasih atas kesabaran Anda.");
        }

        if ($this->isNewerVersion($newLatestVersion, $oldLatestVersion)) {
            $this->broadcastAppUpdateNotification(
                $newLatestVersion,
                (bool) $settings['force_update'],
                (string) $settings['update_url']
            );
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public static function defaults(): array
    {
        return [
            'maintenance_mode' => false,
            'maintenance_message' => 'Server sedang dalam perbaikan. Silakan coba lagi nanti.',
            'latest_app_version' => '1.0.0',
            'force_update' => false,
            'update_url' => 'https://play.google.com/store/apps/details?id=com.ministesy.app',

            // Halaman unduh aplikasi (web)
            'download_android_mode' => 'apk', // 'apk' | 'playstore'
            'download_android_apk_path' => null,
            'download_android_apk_name' => null,
            'download_android_apk_size' => null,
            'download_android_apk_uploaded_at' => null,
            'download_android_playstore_url' => '',
            'download_android_version' => '',
            'download_ios_url' => '',
            'download_ios_version' => '',
        ];
    }

    public static function getSettings()
    {
        $file = 'app_settings.json';
        $stored = [];
        if (Storage::disk('local')->exists($file)) {
            $stored = json_decode(Storage::disk('local')->get($file), true) ?? [];
        }
        // Gabungkan dengan default agar key baru selalu tersedia walau file lama belum punya.
        return array_merge(self::defaults(), $stored);
    }

    private function saveSettings(array $settings): void
    {
        Storage::disk('local')->put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));
    }

    /**
     * Simpan pengaturan halaman unduh aplikasi (APK / link store).
     * Dipisah dari update() agar tidak memicu broadcast notifikasi FCM.
     */
    public function updateDownload(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('manage_rbac')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'download_android_mode' => 'required|in:apk,playstore',
            'download_android_apk' => 'nullable|file|max:204800', // maks 200 MB
            'download_android_playstore_url' => 'nullable|url',
            'download_android_version' => 'nullable|string|max:50',
            'download_ios_url' => 'nullable|url',
            'download_ios_version' => 'nullable|string|max:50',
        ], [
            'download_android_apk.max' => 'Ukuran APK maksimal 200 MB.',
            'download_android_playstore_url.url' => 'Link Play Store tidak valid.',
            'download_ios_url.url' => 'Link App Store tidak valid.',
        ]);

        // Deteksi MIME APK tidak konsisten (apk = arsip zip), jadi validasi via ekstensi.
        if ($request->hasFile('download_android_apk')) {
            $ext = strtolower($request->file('download_android_apk')->getClientOriginalExtension());
            if ($ext !== 'apk') {
                return redirect()->route('settings.index')
                    ->withErrors(['download_android_apk' => 'File harus berekstensi .apk'])
                    ->withInput();
            }
        }

        $settings = $this->getSettings();

        $settings['download_android_mode'] = $request->input('download_android_mode');
        $settings['download_android_playstore_url'] = $request->input('download_android_playstore_url', '');
        $settings['download_android_version'] = $request->input('download_android_version', '');
        $settings['download_ios_url'] = $request->input('download_ios_url', '');
        $settings['download_ios_version'] = $request->input('download_ios_version', '');

        if ($request->hasFile('download_android_apk')) {
            $file = $request->file('download_android_apk');

            // Hapus APK lama agar tidak menumpuk di storage.
            if (!empty($settings['download_android_apk_path'])
                && Storage::disk('local')->exists($settings['download_android_apk_path'])) {
                Storage::disk('local')->delete($settings['download_android_apk_path']);
            }

            $storedName = 'mini_stesy_' . now()->format('Ymd_His') . '_' . \Illuminate\Support\Str::random(8) . '.apk';
            $path = $file->storeAs('apk', $storedName, 'local');

            $settings['download_android_apk_path'] = $path;
            $settings['download_android_apk_name'] = $file->getClientOriginalName();
            $settings['download_android_apk_size'] = $file->getSize();
            $settings['download_android_apk_uploaded_at'] = now()->toDateTimeString();
        }

        $this->saveSettings($settings);

        return redirect()->route('settings.index')->with('success', 'Pengaturan unduhan aplikasi berhasil disimpan.');
    }

    private function broadcastMaintenanceNotification($title, $body)
    {
        try {
            $tokens = DB::table('fcm_tokens')->pluck('fcm_token')->toArray();
            
            if (count($tokens) > 0) {
                $fcm = new FcmService();
                
                // Kirim notifikasi menggunakan broadcastNotification milik FcmService
                // Asumsi parameter: $title, $body, $data, $tokens
                // Karena kita belum yakin struktur lengkapnya, kita pakai perulangan saja
                foreach ($tokens as $token) {
                    $fcm->sendNotification($token, $title, $body, [
                        'type' => 'maintenance_info',
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi maintenance: ' . $e->getMessage());
        }
    }

    private function broadcastAppUpdateNotification(string $latestVersion, bool $forceUpdate, string $updateUrl)
    {
        try {
            $tokens = DB::table('fcm_tokens')->pluck('fcm_token')->toArray();

            if (count($tokens) === 0) {
                return;
            }

            $fcm = new FcmService();
            $title = 'Pembaruan Aplikasi Tersedia';
            $body = $forceUpdate
                ? "Versi {$latestVersion} wajib dipasang untuk melanjutkan penggunaan aplikasi."
                : "Versi {$latestVersion} telah tersedia. Buka aplikasi untuk memperbarui.";

            foreach ($tokens as $token) {
                $fcm->sendNotification($token, $title, $body, [
                    'type' => 'app_update',
                    'latest_app_version' => $latestVersion,
                    'force_update' => $forceUpdate ? '1' : '0',
                    'update_url' => $updateUrl,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Gagal mengirim notifikasi update aplikasi: ' . $e->getMessage());
        }
    }

    private function isNewerVersion(?string $newVersion, ?string $oldVersion): bool
    {
        $cleanNew = $this->cleanVersion($newVersion);
        $cleanOld = $this->cleanVersion($oldVersion);

        if ($cleanNew === '') {
            return false;
        }

        if ($cleanOld === '') {
            return true;
        }

        return version_compare($cleanNew, $cleanOld, '>');
    }

    private function cleanVersion(?string $version): string
    {
        $version = trim((string) $version);
        $version = ltrim($version, 'vV');
        return explode('+', $version)[0] ?? '';
    }
}
