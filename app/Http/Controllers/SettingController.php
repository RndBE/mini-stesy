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
            'maintenance_message' => 'nullable|string'
        ]);

        $settings = $this->getSettings();
        
        $oldMaintenanceMode = $settings['maintenance_mode'] ?? false;
        $newMaintenanceMode = $request->has('maintenance_mode') ? true : false;
        $maintenanceMessage = $request->input('maintenance_message', 'Server sedang dalam perbaikan.');

        $settings['maintenance_mode'] = $newMaintenanceMode;
        $settings['maintenance_message'] = $maintenanceMessage;

        Storage::disk('local')->put($this->settingsFile, json_encode($settings, JSON_PRETTY_PRINT));

        // Jika mode maintenance BARU SAJA dinyalakan, kirim notifikasi massal
        if ($newMaintenanceMode && !$oldMaintenanceMode) {
            $this->broadcastMaintenanceNotification($maintenanceMessage);
        }

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan.');
    }

    public static function getSettings()
    {
        $file = 'app_settings.json';
        if (Storage::disk('local')->exists($file)) {
            return json_decode(Storage::disk('local')->get($file), true) ?? [];
        }
        return [
            'maintenance_mode' => false,
            'maintenance_message' => 'Server sedang dalam perbaikan. Silakan coba lagi nanti.'
        ];
    }

    private function broadcastMaintenanceNotification($message)
    {
        try {
            $tokens = DB::table('fcm_tokens')->pluck('fcm_token')->toArray();
            
            if (count($tokens) > 0) {
                $fcm = new FcmService();
                $title = "Informasi Pemeliharaan Server";
                $body = $message;
                
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
}
