<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    public function log(string $action, Model $model, ?array $old = null, ?array $new = null): AuditLog
    {
        $tenantId = $model->getAttribute('tenant_id') ?? TenantContext::getInstance()->getTenantId();

        return AuditLog::query()->create([
            'tenant_id' => $tenantId,
            'user_id' => Auth::id(),
            'organization_id' => Auth::user()?->current_organization_id ?? null,
            'action' => $action,
            'auditable_type' => $model->getMorphClass(),
            'auditable_id' => $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
