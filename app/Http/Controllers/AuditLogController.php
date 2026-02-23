<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('audit_logs')) {
            return view('audit-log.index', [
                'title' => 'Log Audit',
                'auditLogs' => collect(),
                'modules' => collect(),
            ]);
        }

        $auditLogs = AuditLog::query()
            ->with('actor:id_user,nama,username,level_user')
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->limit(500)
            ->get()
            ->map(fn(AuditLog $log) => $this->transformForView($log))
            ->values();

        return view('audit-log.index', [
            'title' => 'Log Audit',
            'auditLogs' => $auditLogs,
            'modules' => $auditLogs->pluck('module')->unique()->values(),
        ]);
    }

    // public function clientExport(Request $request)
    // {
    //     $request->validate([
    //         'module' => 'nullable|string|max:100',
    //         'target' => 'nullable|string|max:255',
    //         'description' => 'nullable|string|max:500',
    //         'metadata' => 'nullable|array',
    //     ]);

    //     // Data akan dicatat oleh AuditLogMiddleware + AuditLogService.
    //     return response()->json(['success' => true]);
    // }

    private function transformForView(AuditLog $log): array
    {
        return [
            'id' => 'AUD-' . str_pad((string) $log->id, 6, '0', STR_PAD_LEFT),
            'occurred_at' => optional($log->occurred_at)->toDateTimeString()
                ?? optional($log->created_at)->toDateTimeString(),
            'module' => $log->module,
            'action_type' => strtoupper((string) $log->action_type),
            'activity' => $log->activity,
            'target' => $log->target ?: '-',
            'status' => strtoupper((string) $log->status),
            'ip_address' => $log->ip_address ?: '-',
            'user_agent' => $log->user_agent ?: '-',
            'description' => $log->description ?: '-',
            'actor' => [
                'name' => $log->actor?->nama ?? $log->actor_name ?? 'Pengguna Sistem',
                'username' => $log->actor?->username ?? $log->actor_username ?? 'system',
                'role' => $log->actor?->level_user ?? $log->actor_role ?? 'unknown',
            ],
            'metadata' => $log->metadata ?? [],
        ];
    }
}
