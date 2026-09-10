<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * HTTP Basic Auth untuk API integrasi, dicocokkan ke tabel `t_user` lewat
 * kolom `username`. Memakai onceBasic() supaya stateless: tidak ada session
 * atau cookie yang dibuat, cocok untuk pemanggil server-to-server.
 *
 * Kredensial yang dipakai menentukan cakupan data: controller menyaring
 * logger dengan t_Logger::scopeForUser() atas user ini, jadi satu kredensial
 * hanya bisa menarik logger yang menjadi haknya.
 */
class BasicAuthIntegrasi
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            Auth::onceBasic('username');
        } catch (UnauthorizedHttpException) {
            return $this->tolak('Kredensial tidak valid.');
        }

        $user = $request->user();

        // Akun suspend / non-aktif berhenti menarik data walaupun username dan
        // password-nya masih benar.
        if (! $user || ! $user->isActive()) {
            return $this->tolak('Akun tidak aktif. Hubungi Administrator.');
        }

        return $next($request);
    }

    private function tolak(string $pesan): Response
    {
        return response()
            ->json(['status' => false, 'pesan' => $pesan], 401)
            ->header('WWW-Authenticate', 'Basic realm="Mini-STESY Integrasi"');
    }
}
