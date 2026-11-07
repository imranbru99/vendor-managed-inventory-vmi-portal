<?php

use App\Enums\OrganizationType;
use App\Models\Organization;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Modules\ModuleManager;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('buyer tenant A cannot see buyer tenant B products via API', function (): void {
    $tenantA = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant A',
        'slug' => 'tenant-a-'.uniqid(),
        'status' => 'active',
    ]);

    $tenantB = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant B',
        'slug' => 'tenant-b-'.uniqid(),
        'status' => 'active',
    ]);

    $buyerA = Organization::query()->create([
        'tenant_id' => $tenantA->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Buyer A',
        'slug' => 'buyer-a',
        'status' => 'active',
    ]);

    $buyerB = Organization::query()->create([
        'tenant_id' => $tenantB->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Buyer B',
        'slug' => 'buyer-b',
        'status' => 'active',
    ]);

    Product::query()->create([
        'tenant_id' => $tenantA->id,
        'organization_id' => $buyerA->id,
        'sku' => 'A-ONLY',
        'name' => 'Tenant A Product',
        'slug' => 'tenant-a-product',
    ]);

    Product::query()->create([
        'tenant_id' => $tenantB->id,
        'organization_id' => $buyerB->id,
        'sku' => 'B-ONLY',
        'name' => 'Tenant B Product',
        'slug' => 'tenant-b-product',
    ]);

    $userA = User::factory()->create([
        'current_tenant_id' => $tenantA->id,
        'current_organization_id' => $buyerA->id,
    ]);

    Sanctum::actingAs($userA);

    app(ModuleManager::class)->enable($tenantA->id, 'catalog');

    $response = $this->getJson('/api/v1/products');

    $response->assertOk();
    $skus = collect($response->json('data'))->pluck('sku')->all();

    expect($skus)->toContain('A-ONLY')
        ->and($skus)->not->toContain('B-ONLY');
});
