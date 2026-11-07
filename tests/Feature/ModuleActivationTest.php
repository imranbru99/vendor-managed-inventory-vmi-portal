<?php

use App\Models\Module;
use App\Models\Tenant;
use App\Models\TenantModule;
use App\Models\User;
use App\Services\Modules\ModuleRegistry;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('disabled catalog module middleware blocks product API', function (): void {
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Module Tenant',
        'slug' => 'module-tenant-'.uniqid(),
        'status' => 'active',
    ]);

    $user = User::factory()->create([
        'current_tenant_id' => $tenant->id,
    ]);

    Sanctum::actingAs($user);

    $catalogModule = Module::query()->firstOrCreate(
        ['slug' => 'catalog'],
        [
            'name' => 'Product Catalog',
            'category' => 'commerce',
            'is_required' => false,
            'sort_order' => 50,
        ]
    );

    TenantModule::query()->updateOrCreate(
        ['tenant_id' => $tenant->id, 'module_id' => $catalogModule->id],
        ['is_enabled' => false, 'disabled_at' => now()]
    );

    expect(app(ModuleRegistry::class)->get('catalog'))->not->toBeNull();

    $this->getJson('/api/v1/products')
        ->assertForbidden();
});
