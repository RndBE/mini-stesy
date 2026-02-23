<?php

namespace App\Http\Middleware;

use App\Services\AuditLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    public function __construct(
        private AuditLogService $auditLogService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $actorBefore = $request->user();
        $response = $next($request);
        $actorAfter = auth()->user();

        $this->auditLogService->logRequest($request, $response, $actorBefore, $actorAfter);

        return $response;
    }
}
