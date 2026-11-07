<?php

use App\Enums\OrganizationType;
use App\Enums\RelationshipStatus;
use App\Models\BuyerSupplierRelationship;
use App\Models\Organization;
use App\Models\PurchaseOrder;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Modules\ModuleManager;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

test('supplier cannot access purchase order for unrelated buyer relationship', function (): void {
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Access Tenant',
        'slug' => 'access-tenant-'.uniqid(),
        'status' => 'active',
    ]);

    $buyerA = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Buyer A',
        'slug' => 'buyer-a-access',
        'status' => 'active',
    ]);

    $buyerB = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Buyer B',
        'slug' => 'buyer-b-access',
        'status' => 'active',
    ]);

    $supplier = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Supplier,
        'name' => 'Atlas Supplier',
        'slug' => 'atlas-access',
        'status' => 'active',
    ]);

    $related = BuyerSupplierRelationship::query()->create([
        'buyer_organization_id' => $buyerA->id,
        'supplier_organization_id' => $supplier->id,
        'status' => RelationshipStatus::Approved,
    ]);

    $unrelated = BuyerSupplierRelationship::query()->create([
        'buyer_organization_id' => $buyerB->id,
        'supplier_organization_id' => Organization::query()->create([
            'tenant_id' => $tenant->id,
            'type' => OrganizationType::Supplier,
            'name' => 'Other Supplier',
            'slug' => 'other-supplier',
            'status' => 'active',
        ])->id,
        'status' => RelationshipStatus::Approved,
    ]);

    $visiblePo = PurchaseOrder::query()->create([
        'tenant_id' => $tenant->id,
        'relationship_id' => $related->id,
        'po_number' => 'PO-VISIBLE',
        'status' => 'submitted',
    ]);

    $hiddenPo = PurchaseOrder::query()->create([
        'tenant_id' => $tenant->id,
        'relationship_id' => $unrelated->id,
        'po_number' => 'PO-HIDDEN',
        'status' => 'submitted',
    ]);

    $supplierUser = User::factory()->create([
        'current_tenant_id' => $tenant->id,
        'current_organization_id' => $supplier->id,
    ]);

    $modules = app(ModuleManager::class);
    $modules->enable($tenant->id, 'suppliers');
    $modules->enable($tenant->id, 'catalog');
    $modules->enable($tenant->id, 'procurement');

    Sanctum::actingAs($supplierUser);

    $this->getJson('/api/v1/purchase-orders/'.$visiblePo->id)
        ->assertOk();

    $this->getJson('/api/v1/purchase-orders/'.$hiddenPo->id)
        ->assertForbidden();
});
