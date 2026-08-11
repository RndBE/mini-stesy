<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\t_User;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class AuditLogService
{
    private static ?bool $auditTableReady = null;
    private const AUDITED_ACTIONS = ['LOGIN', 'LOGOUT', 'EXPORT', 'CREATE', 'UPDATE', 'DELETE'];

    public function logRequest(
        Request $request,
        Response $response,
        ?t_User $actorBefore = null,
        ?t_User $actorAfter = null
    ): void {
        if (!$this->canLog($request, $actorBefore, $actorAfter)) {
            return;
        }

        $route = $request->route();
        $routeName = (string) ($route?->getName() ?? '');
        $actionType = $this->resolveActionType($request, $routeName);
        $status = $this->resolveStatus($request, $response, $routeName, $actorBefore, $actorAfter);
        $actor = $actorAfter ?? $actorBefore;
        $routeParams = $this->normalizeRouteParams($route?->parameters() ?? []);

        try {
            AuditLog::create([
                'user_id' => $actor?->id_user,
                'module' => $this->resolveModule($request, $routeName),
                'action_type' => $actionType,
                'activity' => $this->resolveActivity($actionType, $status),
                'target' => $this->resolveTarget($request, $routeName, $routeParams),
                'status' => $status,
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 5000, ''),
                'description' => $this->resolveDescription($request, $response, $actionType),
                'metadata' => [
                    'route_name' => $routeName,
                    'route_uri' => $route?->uri(),
                    'route_action_method' => $route?->getActionMethod(),
                    'http_method' => strtoupper($request->method()),
                    'status_code' => $response->getStatusCode(),
                    'route_params' => $routeParams,
                    'query' => $request->query(),
                    'payload_keys' => $this->extractPayloadKeys($request),
                ],
                'occurred_at' => now(),
                'actor_name' => $actor?->nama ?? ($request->input('username') ?: 'Guest'),
                'actor_username' => $actor?->username ?? ($request->input('username') ?: 'guest'),
                'actor_role' => $actor?->level_user ?? 'guest',
            ]);
        } catch (Throwable) {
            // Jangan gagalkan request utama jika penyimpanan audit log bermasalah.
        }
    }

    private function canLog(Request $request, ?t_User $actorBefore, ?t_User $actorAfter): bool
    {
        if (!$this->isAuditTableReady()) {
            return false;
        }

        if ($request->isMethod('OPTIONS')) {
            return false;
        }

        if (!$request->route()) {
            return false;
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        if ($this->isIgnoredRoute($routeName, $request)) {
            return false;
        }

        $actionType = $this->resolveActionType($request, $routeName);
        if (!in_array($actionType, self::AUDITED_ACTIONS, true)) {
            return false;
        }

        if ($actionType === 'LOGIN') {
            return true;
        }

        return (bool) ($actorBefore || $actorAfter);
    }

    private function isAuditTableReady(): bool
    {
        if (self::$auditTableReady === null) {
            self::$auditTableReady = Schema::hasTable('audit_logs');
        }

        return self::$auditTableReady;
    }

    private function resolveActionType(Request $request, string $routeName): string
    {
        $method = strtoupper($request->method());

        if ($routeName === 'audit-log.client-export') {
            return 'EXPORT';
        }

        if ($this->isLoginRequest($request, $routeName)) {
            return 'LOGIN';
        }

        if ($this->isLogoutRequest($request, $routeName)) {
            return 'LOGOUT';
        }

        if ($this->isExportRequest($request, $routeName)) {
            return 'EXPORT';
        }

        return match ($method) {
            'POST' => 'CREATE',
            'PUT', 'PATCH' => 'UPDATE',
            'DELETE' => 'DELETE',
            default => 'VIEW',
        };
    }

    private function resolveStatus(
        Request $request,
        Response $response,
        string $routeName,
        ?t_User $actorBefore,
        ?t_User $actorAfter
    ): string {
        if ($routeName === 'password.update') {
            if ($this->hasPasswordUpdateValidationError($request)) {
                return 'FAILED';
            }

            if ($this->isPasswordUpdated($request)) {
                return 'SUCCESS';
            }
        }

        if ($this->isLoginRequest($request, $routeName)) {
            return $actorAfter ? 'SUCCESS' : 'FAILED';
        }

        if ($this->isLogoutRequest($request, $routeName)) {
            return $actorBefore ? 'SUCCESS' : 'FAILED';
        }

        if ($this->isMutatingRequest($request) && $this->hasAnyValidationError($request)) {
            return 'FAILED';
        }

        return $response->getStatusCode() >= 400 ? 'FAILED' : 'SUCCESS';
    }

    private function hasPasswordUpdateValidationError(Request $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        $errors = $request->session()->get('errors');
        if (!$errors instanceof ViewErrorBag) {
            return false;
        }

        $bag = $errors->getBag('updatePassword');
        if (!$bag || !$bag->any()) {
            return false;
        }

        return $bag->has('current_password')
            || $bag->has('password')
            || $bag->has('password_confirmation');
    }

    private function isPasswordUpdated(Request $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        return $request->session()->get('status') === 'password-updated';
    }

    private function hasAnyValidationError(Request $request): bool
    {
        if (!$request->hasSession()) {
            return false;
        }

        $errors = $request->session()->get('errors');
        if (!$errors instanceof ViewErrorBag) {
            return false;
        }

        foreach ($errors->getBags() as $bag) {
            if ($bag && $bag->any()) {
                return true;
            }
        }

        return false;
    }

    private function isMutatingRequest(Request $request): bool
    {
        return in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function resolveModule(Request $request, string $routeName): string
    {
        $path = strtolower(trim($request->path(), '/'));
        $name = strtolower($routeName);

        if ($routeName === 'audit-log.client-export') {
            $module = trim((string) $request->input('module', 'Data Masuk'));
            return Str::limit($module !== '' ? $module : 'Data Masuk', 100, '');
        }

        if ($this->isLoginRequest($request, $routeName) || $this->isLogoutRequest($request, $routeName)) {
            return 'Autentikasi';
        }

        if (
            Str::startsWith($name, ['device.storedataperangkat', 'device.updatedataperangkat', 'device.data'])
            || Str::startsWith($path, 'data-perangkat')
        ) {
            return 'Data Perangkat';
        }

        if (Str::startsWith($name, 'device.') || Str::startsWith($path, 'pengaturan-device')) {
            return 'Pengaturan Device';
        }

        if (Str::startsWith($name, 'tingkat-siaga-awlr.') || Str::startsWith($path, 'tingkat-siaga-awlr')) {
            return 'Tingkat Siaga AWLR';
        }

        if (Str::startsWith($name, 'peta.')) {
            return 'Peta Lokasi';
        }

        if (Str::startsWith($name, 'analisa.')) {
            return 'Analisa';
        }

        if (Str::startsWith($name, 'data-masuk.')) {
            return 'Data Masuk';
        }

        if (Str::startsWith($name, 'instansi.')) {
            return 'Instansi';
        }

        if (Str::startsWith($name, 'kategori.')) {
            return 'Kategori Logger';
        }

        if (Str::startsWith($name, 'list-parameter.')) {
            return 'List Parameter';
        }

        if (Str::startsWith($name, 'parameter-group.')) {
            return 'Parameter Group';
        }

        if (Str::startsWith($name, 'template-kategori-parameter.')) {
            return 'Template Kategori Parameter';
        }

        if (Str::startsWith($name, 'roles.')) {
            return 'RBAC Role';
        }

        if (Str::startsWith($name, 'permissions.')) {
            return 'RBAC Permission';
        }

        if (Str::startsWith($name, 'users.')) {
            return 'Manajemen User';
        }

        if (Str::startsWith($name, 'profile.')) {
            return 'Profil';
        }

        if (Str::startsWith($name, 'download.')) {
            return 'Download';
        }

        if (Str::startsWith($name, 'manual-book.')) {
            return 'Manual Book';
        }

        if (Str::startsWith($name, 'audit-log.')) {
            return 'Audit Log';
        }

        if (Str::startsWith($name, 'realtime.')) {
            return 'Realtime Monitoring';
        }

        if (Str::startsWith($name, 'beranda')) {
            return 'Beranda';
        }

        if (Str::startsWith($name, 'dashboard')) {
            return 'Dashboard';
        }

        if ($name !== '') {
            return Str::headline(Str::before($name, '.'));
        }

        return Str::headline(Str::before($path ?: 'sistem', '/'));
    }

    private function resolveActivity(string $actionType, string $status): string
    {
        if ($actionType === 'LOGIN') {
            return $status === 'SUCCESS' ? 'Login berhasil' : 'Login gagal';
        }

        if ($actionType === 'LOGOUT') {
            return $status === 'SUCCESS' ? 'Logout berhasil' : 'Logout gagal';
        }

        return match ($actionType) {
            'CREATE' => 'Menambahkan data',
            'UPDATE' => 'Memperbarui data',
            'DELETE' => 'Menghapus data',
            'EXPORT' => 'Mengekspor data',
            default => 'Melihat halaman',
        };
    }

    private function resolveDescription(Request $request, Response $response, string $actionType): string
    {
        $route = $request->route();
        $method = strtoupper($request->method());
        $routeName = (string) ($route?->getName() ?? '-');
        $actionMethod = (string) ($route?->getActionMethod() ?? '-');

        if ($routeName === 'audit-log.client-export') {
            $description = trim((string) $request->input('description', 'Export data dari sisi client.'));
            return Str::limit($description, 500, '');
        }

        return "Aksi {$actionType} melalui {$method} pada route {$routeName} ({$actionMethod}), status HTTP {$response->getStatusCode()}.";
    }

    private function resolveTarget(Request $request, string $routeName, array $routeParams): string
    {
        $name = strtolower($routeName);

        if ($routeName === 'audit-log.client-export') {
            $target = trim((string) $request->input('target', 'Data CSV'));
            return Str::limit($target !== '' ? $target : 'Data CSV', 255, '');
        }

        if ($this->isLoginRequest($request, $routeName) || $this->isLogoutRequest($request, $routeName)) {
            return 'Aplikasi Mini Stesy';
        }

        if ($name === 'device.store') {
            $lokasi = trim((string) $request->input('nama_lokasi', ''));
            if ($lokasi !== '') {
                return Str::limit("Lokasi: {$lokasi}", 255, '');
            }
        }

        if ($name === 'device.update') {
            $loggerId = (string) ($routeParams['id'] ?? '');
            if ($loggerId !== '') {
                return Str::limit("Logger: {$loggerId}", 255, '');
            }
        }

        if ($name === 'device.storedataperangkat') {
            $loggerId = trim((string) $request->input('id_logger', ''));
            if ($loggerId !== '') {
                return Str::limit("Logger: {$loggerId}", 255, '');
            }
        }

        if ($name === 'device.updatedataperangkat') {
            $loggerId = (string) ($routeParams['id'] ?? '');
            if ($loggerId !== '') {
                return Str::limit("Logger: {$loggerId}", 255, '');
            }
        }

        if ($name === 'tingkat-siaga-awlr.update') {
            $loggerId = (string) ($routeParams['idLogger'] ?? '');
            if ($loggerId !== '') {
                return Str::limit("Logger: {$loggerId}", 255, '');
            }
        }

        $base = $routeName !== '' ? $routeName : $request->path();
        $params = collect($routeParams)
            ->map(fn($value, $key) => "{$key}:{$value}")
            ->values()
            ->all();

        if (!empty($params)) {
            $base .= ' [' . implode(', ', $params) . ']';
        }

        return Str::limit($base, 255, '');
    }

    private function normalizeRouteParams(array $params): array
    {
        $normalized = [];

        foreach ($params as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $normalized[$key] = $value;
                continue;
            }

            if (is_object($value) && method_exists($value, 'getKey')) {
                $normalized[$key] = $value->getKey();
                continue;
            }

            $normalized[$key] = (string) $value;
        }

        return $normalized;
    }

    private function extractPayloadKeys(Request $request): array
    {
        if ($request->isMethod('GET')) {
            return [];
        }

        $sensitive = [
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
            'token',
        ];

        return array_values(array_slice(
            array_keys($request->except($sensitive)),
            0,
            50
        ));
    }

    private function isLoginRequest(Request $request, string $routeName): bool
    {
        return $request->isMethod('POST') && ($routeName === 'login' || $request->is('login'));
    }

    private function isLogoutRequest(Request $request, string $routeName): bool
    {
        return $request->isMethod('POST') && ($routeName === 'logout' || $request->is('logout'));
    }

    private function isExportRequest(Request $request, string $routeName): bool
    {
        return Str::contains(strtolower($routeName . ' ' . $request->path()), 'export');
    }

    private function isIgnoredRoute(string $routeName, Request $request): bool
    {
        $ignoredNames = [
            'up',
            'peta.data',
            'analisa.data',
            'data-masuk.api',
            'realtime.data',
        ];

        if (in_array($routeName, $ignoredNames, true)) {
            return true;
        }

        if (Str::startsWith($routeName, ['ignition.', 'debugbar.'])) {
            return true;
        }

        return $request->is('api/peta/data/*')
            || $request->is('api/analisa/data/*')
            || $request->is('api/data-masuk')
            || $request->is('realtime-monitoring/data/*');
    }
}
