<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\Tenant;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = TenantContext::getInstance();

        $user = $request->user();

        if ($user !== null) {
            if ($user->current_tenant_id !== null) {
                $tenant = Tenant::query()->find($user->current_tenant_id);

                if ($tenant !== null) {
                    $context->setTenant($tenant);
                }
            }

            if ($user->current_organization_id !== null) {
                $organization = Organization::query()->find($user->current_organization_id);

                if ($organization !== null) {
                    $context->setOrganization($organization);
                }
            }
        }

        try {
            return $next($request);
        } finally {
            $context->clear();
        }
    }
}
