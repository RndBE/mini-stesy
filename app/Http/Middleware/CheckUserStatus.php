<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->status === 'suspend') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah ditangguhkan (suspend).' . ($user->suspend_reason ? ' Alasan: ' . $user->suspend_reason : ' Silakan hubungi Administrator.'),
                ], 403);
            }

            if ($user->status === 'non-aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda saat ini non-aktif. Silakan hubungi Administrator untuk mengaktifkan kembali.',
                ], 403);
            }
        }

        return $next($request);
    }
}
