<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetFilamentTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = TenantContext::getInstance();
        $user = $request->user();

        if ($user?->current_tenant_id) {
            $user->loadMissing('currentTenant');
            $context->setTenant($user->currentTenant);
        }

        if ($user?->current_organization_id) {
            $user->loadMissing('currentOrganization');
            $context->setOrganization($user->currentOrganization);
        }

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
