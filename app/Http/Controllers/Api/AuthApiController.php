<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\t_User;

class AuthApiController extends Controller
{
    /**
     * POST /api/v1/mobile/auth/login
     * Login dan dapatkan Sanctum token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari user di tabel t_user berdasarkan kolom username
        $user = t_User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        // MAINTENANCE SWITCH: Cek apakah server sedang maintenance
        $settings = \App\Http\Controllers\SettingController::getSettings();
        if (($settings['maintenance_mode'] ?? false) == true) {
            // Izinkan Superadmin untuk tetap bisa login saat maintenance
            if (!$user->isSuperAdmin() && !$user->hasPermission('manage_rbac')) {
                return response()->json([
                    'success' => false,
                    'message' => 'maintenance: ' . ($settings['maintenance_message'] ?? 'Server sedang dalam perbaikan.'),
                ], 403);
            }
        }

        // KILL SWITCH: Cek apakah akun tersuspend atau tidak aktif
        if ($user->isSuspended()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah di-suspend' . ($user->suspend_reason ? ' karena ' . $user->suspend_reason : '. Silakan hubungi Administrator.'),
            ], 403);
        }

        if ($user->isNonActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda saat ini non-aktif. Silakan hubungi Administrator untuk mengaktifkan kembali.',
            ], 403);
        }

        // Hapus token lama (opsional: satu device satu token)
        // $user->tokens()->delete();

        $token = $user->createToken('mobile-app', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'expires_in' => 30 * 24 * 60 * 60, // seconds
                'user' => [
                    'id_user'    => $user->id_user,
                    'nama'       => $user->nama,
                    'username'   => $user->username,
                    'level_user' => $user->level_user,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/mobile/auth/logout
     * Revoke current token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * GET /api/v1/mobile/auth/me
     * Info user yang sedang login.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id_user'    => $user->id_user,
                'nama'       => $user->nama,
                'username'   => $user->username,
                'level_user' => $user->level_user,
            ],
        ]);
    }

    /**
     * GET /api/v1/mobile/auth/config
     * Konfigurasi aplikasi untuk force update dll.
     */
    public function appConfig()
    {
        $settings = \App\Http\Controllers\SettingController::getSettings();
        
        return response()->json([
            'success' => true,
            'data' => [
                'latest_app_version' => $settings['latest_app_version'] ?? '1.0.0',
                'force_update' => $settings['force_update'] ?? false,
                'update_url' => $settings['update_url'] ?? 'https://play.google.com/store/apps/details?id=com.ministesy.app',
            ],
        ]);
    }
}
