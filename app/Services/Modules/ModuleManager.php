<?php

namespace App\Services\Modules;

use App\Models\Module;
use App\Models\TenantModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class ModuleManager
{
    public function __construct(
        private readonly ModuleRegistry $registry,
    ) {}

    public function isEnabled(int $tenantId, string $moduleSlug): bool
    {
        $definition = $this->registry->get($moduleSlug);

        if ($definition === null) {
            return false;
        }

        if ($definition['is_required']) {
            return true;
        }

        return TenantModule::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('module', fn ($query) => $query->where('slug', $moduleSlug))
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * @return Collection<int, string>
     */
    public function enabledSlugs(int $tenantId): Collection
    {
        $required = collect($this->registry->all())
            ->filter(fn (array $def): bool => $def['is_required'])
            ->keys();

        $enabled = TenantModule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->with('module:id,slug')
            ->get()
            ->pluck('module.slug');

        return $required->merge($enabled)->unique()->values();
    }

    public function enable(int $tenantId, string $moduleSlug): TenantModule
    {
        $definition = $this->registry->get($moduleSlug);

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown module slug: {$moduleSlug}");
        }

        foreach ($definition['dependencies'] as $dependency) {
            if (! $this->isEnabled($tenantId, $dependency)) {
                throw new RuntimeException(
                    "Cannot enable \"{$moduleSlug}\": dependency \"{$dependency}\" is not enabled."
                );
            }
        }

        return DB::transaction(function () use ($tenantId, $moduleSlug, $definition): TenantModule {
            $module = Module::query()->firstOrCreate(
                ['slug' => $moduleSlug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'category' => $definition['category'],
                    'is_required' => $definition['is_required'],
                    'dependencies' => $definition['dependencies'],
                    'sort_order' => $definition['sort_order'],
                ]
            );

            return TenantModule::query()->updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'module_id' => $module->id,
                ],
                [
                    'is_enabled' => true,
                    'enabled_at' => now(),
                    'disabled_at' => null,
                ]
            );
        });
    }

    public function disable(int $tenantId, string $moduleSlug): ?TenantModule
    {
        $definition = $this->registry->get($moduleSlug);

        if ($definition === null) {
            throw new InvalidArgumentException("Unknown module slug: {$moduleSlug}");
        }

        if ($definition['is_required']) {
            throw new RuntimeException("Cannot disable required module: {$moduleSlug}");
        }

        $dependents = $this->dependentsOf($moduleSlug);

        foreach ($dependents as $dependent) {
            if ($this->isEnabled($tenantId, $dependent)) {
                throw new RuntimeException(
                    "Cannot disable \"{$moduleSlug}\": module \"{$dependent}\" depends on it."
                );
            }
        }

        $tenantModule = TenantModule::query()
            ->where('tenant_id', $tenantId)
            ->whereHas('module', fn ($query) => $query->where('slug', $moduleSlug))
            ->first();

        if ($tenantModule === null) {
            return null;
        }

        $tenantModule->update([
            'is_enabled' => false,
            'disabled_at' => now(),
        ]);

        return $tenantModule->fresh();
    }

    /**
     * @return list<string>
     */
    private function dependentsOf(string $moduleSlug): array
    {
        $dependents = [];

        foreach ($this->registry->all() as $slug => $def) {
            if (in_array($moduleSlug, $def['dependencies'], true)) {
                $dependents[] = $slug;
            }
        }

        return $dependents;
    }
}
