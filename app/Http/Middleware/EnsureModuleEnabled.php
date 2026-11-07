<?php

namespace App\Http\Middleware;

use App\Services\Modules\ModuleManager;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private readonly ModuleManager $moduleManager,
    ) {}

    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $tenantId = TenantContext::getInstance()->getTenantId();

        if ($tenantId === null) {
            abort(403, 'Tenant context is required.');
        }

        if (! $this->moduleManager->isEnabled($tenantId, $moduleSlug)) {
            abort(403, "Module \"{$moduleSlug}\" is not enabled for this tenant.");
        }

        return $next($request);
    }
}
