<?php

use App\Enums\OrganizationType;
use App\Enums\ReplenishmentPolicyType;
use App\Models\InventoryBalance;
use App\Models\Organization;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\SupplierProduct;
use App\Models\Tenant;
use App\Models\User;
use App\Models\VmiAgreement;
use App\Models\VmiPolicy;
use App\Models\Warehouse;
use App\Services\Logistics\ShipmentService;
use App\Services\Procurement\PurchaseOrderService;
use App\Services\Receiving\GoodsReceiptService;
use App\Services\Replenishment\ProposalService;
use App\Services\Replenishment\ReplenishmentEngine;
use App\Services\Suppliers\SupplierRelationshipService;
use App\Support\TenantContext;
use Illuminate\Support\Str;

test('vmi workflow from relationship to inventory receipt', function (): void {
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Workflow Tenant',
        'slug' => 'workflow-'.uniqid(),
        'status' => 'active',
    ]);

    TenantContext::getInstance()->setTenant($tenant);

    $buyer = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Buyer,
        'name' => 'Workflow Buyer',
        'slug' => 'workflow-buyer',
        'status' => 'active',
    ]);

    $supplier = Organization::query()->create([
        'tenant_id' => $tenant->id,
        'type' => OrganizationType::Supplier,
        'name' => 'Workflow Supplier',
        'slug' => 'workflow-supplier',
        'status' => 'active',
    ]);

    $warehouse = Warehouse::query()->create([
        'tenant_id' => $tenant->id,
        'organization_id' => $buyer->id,
        'code' => 'WFH',
        'name' => 'Workflow WH',
    ]);

    $product = Product::query()->create([
        'tenant_id' => $tenant->id,
        'organization_id' => $buyer->id,
        'sku' => 'WF-SKU',
        'name' => 'Workflow Product',
        'slug' => 'workflow-product',
    ]);

    $relationship = app(SupplierRelationshipService::class)->invite($buyer, $supplier);
    app(SupplierRelationshipService::class)->approve($relationship);

    SupplierProduct::query()->create([
        'relationship_id' => $relationship->id,
        'product_id' => $product->id,
        'unit_price' => 12.5,
        'moq' => 6,
        'order_multiple' => 6,
        'lead_time_days' => 5,
    ]);

    $agreement = VmiAgreement::query()->create([
        'tenant_id' => $tenant->id,
        'relationship_id' => $relationship->id,
        'name' => 'WF Agreement',
        'status' => 'active',
    ]);

    VmiPolicy::query()->create([
        'tenant_id' => $tenant->id,
        'agreement_id' => $agreement->id,
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'policy_type' => ReplenishmentPolicyType::MinMax,
        'min_stock' => 40,
        'max_stock' => 160,
        'moq' => 6,
        'order_multiple' => 6,
        'is_active' => true,
    ]);

    InventoryBalance::query()->create([
        'tenant_id' => $tenant->id,
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'on_hand' => 20,
        'sellable' => 20,
    ]);

    $run = app(ReplenishmentEngine::class)->runForRelationship($relationship, 'test');
    $recommendations = $run->recommendations()->where('recommended_qty', '>', 0)->get();
    expect($recommendations)->not->toBeEmpty();

    $user = User::factory()->create([
        'current_tenant_id' => $tenant->id,
        'current_organization_id' => $buyer->id,
    ]);

    $this->actingAs($user);

    $proposal = app(ProposalService::class)->createFromRecommendations($relationship, $recommendations);
    app(ProposalService::class)->submit($proposal);
    app(ProposalService::class)->approve($proposal);

    $po = PurchaseOrder::query()->where('proposal_id', $proposal->id)->firstOrFail();

    app(PurchaseOrderService::class)->issue($po);
    app(PurchaseOrderService::class)->acknowledge($po->fresh('lines'));

    $shipment = app(ShipmentService::class)->createShipment(
        $po->fresh('lines'),
        $po->lines->map(fn ($line) => [
            'purchase_order_line_id' => $line->id,
            'product_id' => $line->product_id,
            'quantity' => $line->quantity,
        ])->all()
    );
    app(ShipmentService::class)->dispatch($shipment);

    app(GoodsReceiptService::class)->receive(
        $po->fresh('lines'),
        $warehouse,
        $po->lines->map(fn ($line) => [
            'purchase_order_line_id' => $line->id,
            'product_id' => $line->product_id,
            'received_qty' => $line->quantity,
        ])->all(),
        $shipment->fresh()
    );

    $balance = InventoryBalance::query()
        ->where('warehouse_id', $warehouse->id)
        ->where('product_id', $product->id)
        ->first();

    expect((float) $balance->sellable)->toBeGreaterThan(20.0);
});
