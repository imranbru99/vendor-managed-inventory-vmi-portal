<?php

namespace App\Support;

use App\Models\Organization;
use App\Models\Tenant;

class TenantContext
{
    private ?Tenant $tenant = null;

    private ?Organization $organization = null;

    public static function getInstance(): self
    {
        return app(self::class);
    }

    public function setTenant(?Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    public function getTenantId(): ?int
    {
        return $this->tenant?->id;
    }

    public function setOrganization(?Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getOrganizationId(): ?int
    {
        return $this->organization?->id;
    }

    public function clear(): void
    {
        $this->tenant = null;
        $this->organization = null;
    }

    public function isSet(): bool
    {
        return $this->tenant !== null;
    }
}
