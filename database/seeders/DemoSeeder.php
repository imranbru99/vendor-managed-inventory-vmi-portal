<?php

namespace Database\Seeders;

use App\Enums\OrganizationType;
use App\Enums\ProposalStatus;
use App\Enums\ReplenishmentPolicyType;
use App\Models\AuditLog;
use App\Models\BlogPost;
use App\Models\BuyerSupplierRelationship;
use App\Models\CmsPage;
use App\Models\DemandEvent;
use App\Models\Faq;
use App\Models\GoodsReceipt;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Module;
use App\Models\Organization;
use App\Models\OrganizationMembership;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\ReplenishmentProposal;
use App\Models\ReplenishmentRecommendation;
use App\Models\Shipment;
use App\Models\SubscriptionPlan;
use App\Models\SupplierProduct;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\UnitOfMeasure;
use App\Models\User;
use App\Models\VmiAgreement;
use App\Models\VmiPolicy;
use App\Models\Warehouse;
use App\Services\Logistics\ShipmentService;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use App\Services\Procurement\PurchaseOrderService;
use App\Services\Receiving\GoodsReceiptService;
use App\Services\Replenishment\ProposalService;
use App\Services\Replenishment\ReplenishmentEngine;
use App\Services\Suppliers\SupplierRelationshipService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    protected string $password;

    protected Tenant $tenant;

    protected Organization $buyer;

    /** @var array<string, Organization> */
    protected array $suppliers = [];

    /** @var array<string, Warehouse> */
    protected array $warehouses = [];

    /** @var list<Product> */
    protected array $products = [];

    public function run(): void
    {
        $this->password = (string) config('vmi.demo.password', config('vmi.demo.admin_password', 'password'));

        DB::transaction(function (): void {
            $this->seedPlatformAdmin();
            $this->seedTenantAndOrganizations();
            $this->syncModules();
            $this->seedSubscriptionPlans();
            $this->seedCatalog();
            $this->seedRelationshipsAndAgreements();
            $this->seedInventoryAndDemand();
            $this->seedCmsContent();
            $this->seedDemoUsers();
            $this->seedReplenishmentArtifacts();
            $this->seedAuditLogs();
        });
    }

    protected function demoPassword(): string
    {
        return $this->password;
    }

    protected function seedPlatformAdmin(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('vmi.demo.admin_email', 'platform@vmi.test')],
            [
                'name' => 'Platform Administrator',
                'password' => $this->demoPassword(),
                'is_platform_admin' => true,
                'email_verified_at' => now(),
            ]
        )->assignRole('Platform Admin');
    }

    protected function seedTenantAndOrganizations(): void
    {
        $this->tenant = Tenant::query()->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Northstar Retail Group',
            'slug' => config('vmi.demo.tenant_slug', 'northstar-retail'),
            'status' => 'active',
        ]);

        $this->buyer = Organization::query()->create([
            'tenant_id' => $this->tenant->id,
            'type' => OrganizationType::Buyer,
            'name' => 'Northstar Retail Group',
            'slug' => 'northstar-retail',
            'legal_name' => 'Northstar Retail Group Inc.',
            'email' => 'procurement@northstar-retail.test',
            'status' => 'active',
            'currency_code' => 'USD',
            'timezone' => 'America/Chicago',
        ]);

        foreach ([
            'Atlas Supply Co.' => 'atlas-supply',
            'Global Distribution Ltd.' => 'global-distribution',
            'Prime Wholesale Partners' => 'prime-wholesale',
        ] as $name => $slug) {
            $this->suppliers[$slug] = Organization::query()->create([
                'tenant_id' => $this->tenant->id,
                'type' => OrganizationType::Supplier,
                'name' => $name,
                'slug' => $slug,
                'status' => 'active',
                'currency_code' => 'USD',
            ]);
        }

        foreach ([
            'CDC' => 'Central Distribution Center',
            'NWH' => 'Northern Warehouse',
            'SWH' => 'Southern Warehouse',
        ] as $code => $name) {
            $this->warehouses[$code] = Warehouse::query()->create([
                'tenant_id' => $this->tenant->id,
                'organization_id' => $this->buyer->id,
                'code' => $code,
                'name' => $name,
                'type' => 'distribution',
                'country_code' => 'US',
                'is_active' => true,
            ]);
        }
    }

    protected function syncModules(): void
    {
        $registry = app(ModuleRegistry::class);
        $manager = app(ModuleManager::class);

        foreach ($registry->all() as $slug => $definition) {
            Module::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => $definition['description'],
                    'icon' => $definition['icon'],
                    'category' => $definition['category']->value,
                    'is_required' => $definition['is_required'],
                    'dependencies' => $definition['dependencies'],
                    'sort_order' => $definition['sort_order'],
                    'is_global_active' => true,
                ]
            );
        }

        $sorted = collect($registry->all())->sortBy('sort_order');
        for ($pass = 0; $pass < 4; $pass++) {
            foreach ($sorted as $slug => $definition) {
                if ($definition['is_required']) {
                    continue;
                }

                try {
                    $manager->enable($this->tenant->id, $slug);
                } catch (\Throwable) {
                    // Retry on later passes when dependencies become enabled.
                }
            }
        }
    }

    protected function seedSubscriptionPlans(): void
    {
        $plans = [
            ['Starter', 'starter', 99, 990],
            ['Professional', 'professional', 299, 2990],
            ['Business', 'business', 599, 5990],
            ['Enterprise', 'enterprise', 999, 9990],
        ];

        $firstPlanId = null;
        foreach ($plans as $index => [$name, $slug, $monthly, $yearly]) {
            $plan = SubscriptionPlan::query()->create([
                'name' => $name,
                'slug' => $slug,
                'description' => "{$name} plan for growing VMI programs.",
                'price_monthly' => $monthly,
                'price_yearly' => $yearly,
                'sort_order' => ($index + 1) * 10,
                'features' => ['vmi', 'replenishment', 'procurement'],
                'is_active' => true,
            ]);
            $firstPlanId ??= $plan->id;
        }

        TenantSubscription::query()->create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $firstPlanId,
            'status' => 'active',
            'starts_at' => now()->subMonths(3),
        ]);
    }

    protected function seedCatalog(): void
    {
        $uomEa = UnitOfMeasure::query()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'EA',
            'name' => 'Each',
            'type' => 'unit',
        ]);
        UnitOfMeasure::query()->create([
            'tenant_id' => $this->tenant->id,
            'code' => 'CS',
            'name' => 'Case',
            'type' => 'case',
        ]);

        $categories = [
            'Grocery', 'Beverages', 'Personal Care', 'Household',
            'Electronics Accessories', 'Automotive', 'Pharma OTC', 'Apparel Basics',
        ];

        $categoryIds = [];
        foreach ($categories as $categoryName) {
            $categoryIds[$categoryName] = ProductCategory::query()->create([
                'tenant_id' => $this->tenant->id,
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
            ])->id;
        }

        $brands = ['Northstar', 'Atlas', 'Prime', 'Global', 'Essential', 'ValuePack'];
        for ($i = 1; $i <= 50; $i++) {
            $categoryName = $categories[($i - 1) % count($categories)];
            $sku = sprintf('NS-%s-%04d', strtoupper(substr(Str::slug($categoryName), 0, 3)), $i);

            $this->products[] = Product::query()->create([
                'tenant_id' => $this->tenant->id,
                'organization_id' => $this->buyer->id,
                'category_id' => $categoryIds[$categoryName],
                'sku' => $sku,
                'name' => "{$categoryName} Item {$i}",
                'slug' => Str::slug("{$categoryName}-item-{$i}"),
                'description' => "Demo {$categoryName} product for replenishment scenarios.",
                'brand' => $brands[$i % count($brands)],
                'base_uom_id' => $uomEa->id,
                'status' => 'active',
            ]);
        }
    }

    protected function seedRelationshipsAndAgreements(): void
    {
        $relationshipService = app(SupplierRelationshipService::class);
        $atlas = $this->suppliers['atlas-supply'];

        $atlasRelationship = $relationshipService->invite($this->buyer, $atlas, 'Primary VMI partner');
        $relationshipService->approve($atlasRelationship);

        foreach (array_slice($this->suppliers, 1) as $supplier) {
            $rel = $relationshipService->invite($this->buyer, $supplier);
            $relationshipService->approve($rel);
        }

        /** @var BuyerSupplierRelationship $atlasRelationship */
        $atlasRelationship = BuyerSupplierRelationship::query()
            ->where('buyer_organization_id', $this->buyer->id)
            ->where('supplier_organization_id', $atlas->id)
            ->firstOrFail();

        $agreement = VmiAgreement::query()->create([
            'tenant_id' => $this->tenant->id,
            'relationship_id' => $atlasRelationship->id,
            'name' => 'Northstar ↔ Atlas VMI Agreement',
            'status' => 'active',
            'starts_at' => now()->subYear(),
            'terms' => 'Standard VMI replenishment with min/max and days-of-supply policies.',
        ]);

        $policyTypes = [
            ReplenishmentPolicyType::MinMax,
            ReplenishmentPolicyType::ReorderPoint,
            ReplenishmentPolicyType::DaysOfSupply,
        ];
        $warehouseList = array_values($this->warehouses);

        foreach (array_slice($this->products, 0, 36) as $index => $product) {
            $warehouse = $warehouseList[$index % count($warehouseList)];
            $type = $policyTypes[$index % count($policyTypes)];
            $min = 50 + ($index % 5) * 10;
            $max = $min * 4;

            VmiPolicy::query()->create([
                'tenant_id' => $this->tenant->id,
                'agreement_id' => $agreement->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'policy_type' => $type,
                'min_stock' => $min,
                'max_stock' => $max,
                'safety_stock' => (int) ($min * 0.5),
                'reorder_point' => (int) ($min * 1.2),
                'target_days_of_supply' => 14 + ($index % 7),
                'lead_time_days' => 5 + ($index % 4),
                'moq' => 12,
                'order_multiple' => 6,
                'max_order_qty' => $max,
                'is_active' => true,
            ]);

            $price = round(5 + ($index % 20) * 1.25, 2);
            SupplierProduct::query()->create([
                'relationship_id' => $atlasRelationship->id,
                'product_id' => $product->id,
                'supplier_sku' => 'ATL-'.$product->sku,
                'unit_price' => $price,
                'currency_code' => 'USD',
                'moq' => 12,
                'order_multiple' => 6,
                'lead_time_days' => 5,
                'is_active' => true,
            ]);
        }

        BuyerSupplierRelationship::query()->with('supplierOrganization')->get()->each(function (BuyerSupplierRelationship $rel) use ($atlasRelationship): void {
            if ($rel->id === $atlasRelationship->id) {
                return;
            }

            foreach (array_slice($this->products, 36, 14) as $product) {
                SupplierProduct::query()->firstOrCreate(
                    [
                        'relationship_id' => $rel->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'supplier_sku' => strtoupper(substr($rel->supplierOrganization->slug, 0, 3)).'-'.$product->sku,
                        'unit_price' => 10.00,
                        'moq' => 6,
                        'order_multiple' => 3,
                        'lead_time_days' => 7,
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    protected function seedInventoryAndDemand(): void
    {
        $policies = VmiPolicy::query()->where('tenant_id', $this->tenant->id)->get();

        foreach ($policies as $index => $policy) {
            $min = (float) $policy->min_stock;
            $max = (float) $policy->max_stock;
            $bucket = $index % 3;

            $sellable = match ($bucket) {
                0 => $min * 0.4,
                1 => ($min + $max) / 2,
                default => $max * 1.35,
            };

            InventoryBalance::query()->create([
                'tenant_id' => $this->tenant->id,
                'warehouse_id' => $policy->warehouse_id,
                'product_id' => $policy->product_id,
                'on_hand' => $sellable,
                'sellable' => $sellable,
                'reserved' => $bucket === 1 ? 5 : 0,
                'allocated' => 0,
                'confirmed_inbound' => $bucket === 0 ? 10 : 0,
            ]);

            for ($day = 0; $day < 30; $day++) {
                DemandEvent::query()->create([
                    'tenant_id' => $this->tenant->id,
                    'product_id' => $policy->product_id,
                    'warehouse_id' => $policy->warehouse_id,
                    'event_date' => Carbon::today()->subDays($day),
                    'quantity' => random_int(2, 18),
                    'source' => 'pos',
                ]);
            }
        }

        InventoryTransaction::query()->create([
            'tenant_id' => $this->tenant->id,
            'warehouse_id' => $this->warehouses['CDC']->id,
            'product_id' => $this->products[0]->id,
            'type' => 'adjustment',
            'quantity' => 25,
            'quantity_before' => 0,
            'quantity_after' => 25,
            'occurred_at' => now()->subDays(2),
            'notes' => 'Opening balance adjustment',
        ]);
    }

    protected function seedReplenishmentArtifacts(): void
    {
        $atlasRelationship = BuyerSupplierRelationship::query()
            ->where('buyer_organization_id', $this->buyer->id)
            ->whereHas('supplierOrganization', fn ($q) => $q->where('slug', 'atlas-supply'))
            ->firstOrFail();

        $engine = app(ReplenishmentEngine::class);
        $run = $engine->runForRelationship($atlasRelationship, 'demo-seeder');

        $recommendations = ReplenishmentRecommendation::query()
            ->where('run_id', $run->id)
            ->where('recommended_qty', '>', 0)
            ->get();

        $buyerAdmin = User::query()->where('email', 'buyer.admin@vmi.test')->first();
        if ($buyerAdmin) {
            Auth::login($buyerAdmin);
        }

        if ($recommendations->isNotEmpty()) {
            $pendingRecs = $recommendations->take(3);
            $proposalService = app(ProposalService::class);
            $pending = $proposalService->createFromRecommendations($atlasRelationship, $pendingRecs, 'PROP-DEMO-PENDING');
            $proposalService->submit($pending);

            $approvedRecs = $recommendations->slice(3, 2);
            if ($approvedRecs->isNotEmpty()) {
                $approved = $proposalService->createFromRecommendations($atlasRelationship, $approvedRecs, 'PROP-DEMO-APPROVED');
                $proposalService->submit($approved);
                $proposalService->approve($approved, 'Approved in demo seed.');
            }
        } else {
            ReplenishmentProposal::query()->create([
                'tenant_id' => $this->tenant->id,
                'relationship_id' => $atlasRelationship->id,
                'proposal_number' => 'PROP-DEMO-PENDING',
                'status' => ProposalStatus::PendingApproval,
                'total_qty' => 48,
                'total_value' => 620.40,
                'submitted_at' => now(),
            ]);
        }

        $po = PurchaseOrder::query()
            ->where('tenant_id', $this->tenant->id)
            ->latest('id')
            ->first();

        if ($po !== null) {
            $poService = app(PurchaseOrderService::class);
            $poService->issue($po);
            $poService->acknowledge($po->fresh('lines'));

            $shipmentService = app(ShipmentService::class);
            $shipment = $shipmentService->createShipment(
                $po->fresh('lines'),
                $po->lines->map(fn ($line) => [
                    'purchase_order_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'quantity' => $line->quantity,
                ])->all(),
                ['carrier' => 'Demo Freight', 'tracking_number' => 'TRK-DEMO-1001']
            );
            $shipmentService->dispatch($shipment);

            $receiptService = app(GoodsReceiptService::class);
            $receiptService->receive(
                $po->fresh('lines'),
                $this->warehouses['CDC'],
                $po->lines->map(fn ($line) => [
                    'purchase_order_line_id' => $line->id,
                    'product_id' => $line->product_id,
                    'received_qty' => $line->quantity,
                ])->all(),
                Shipment::query()->find($shipment->id),
                'Demo goods receipt'
            );

            GoodsReceipt::query()->latest('id')->first();
        }

        Auth::logout();
    }

    protected function seedCmsContent(): void
    {
        CmsPage::query()->create([
            'title' => 'Welcome to the VMI Portal',
            'slug' => 'home',
            'locale' => 'en',
            'excerpt' => 'Supplier-controlled replenishment for modern retail networks.',
            'body' => '<h1>Vendor Managed Inventory</h1><p>Align buyers and suppliers with shared inventory signals, automated replenishment, and governed approvals.</p>',
            'template' => 'homepage',
            'is_published' => true,
            'published_at' => now(),
        ]);

        foreach ([
            ['VMI Policy Design Best Practices', 'vmi-policy-design-best-practices'],
            ['Reducing Stockouts with Replenishment Automation', 'reducing-stockouts-replenishment'],
            ['Supplier Collaboration in Multi-Echelon Networks', 'supplier-collaboration-multi-echelon'],
        ] as [$title, $slug]) {
            BlogPost::query()->create([
                'title' => $title,
                'slug' => $slug,
                'excerpt' => 'Insights from Northstar Retail Group demo data.',
                'body' => "<p>{$title} — demo article content.</p>",
                'is_published' => true,
                'published_at' => now()->subDays(random_int(1, 20)),
            ]);
        }

        $faqItems = [
            ['What is VMI?', 'Vendor Managed Inventory shifts replenishment planning to the supplier within agreed min/max or days-of-supply targets.'],
            ['How are proposals approved?', 'Buyer managers review replenishment proposals before purchase orders are issued to suppliers.'],
            ['Can I disable modules?', 'Tenant admins can enable or disable optional modules from the Module Manager.'],
            ['Which policy types are supported?', 'Min/max, reorder point, and days-of-supply policies are included in the demo tenant.'],
            ['How is inventory position calculated?', 'Sellable plus confirmed inbound minus reserved and allocated.'],
            ['Do suppliers see all buyers?', 'Suppliers only access purchase data for approved buyer relationships.'],
            ['Is multi-warehouse supported?', 'Yes — policies and balances are maintained per warehouse.'],
            ['How do I reset demo data?', 'Run php artisan vmi:demo-seed --fresh to rebuild the demo database.'],
        ];

        foreach ($faqItems as $order => [$question, $answer]) {
            Faq::query()->create([
                'question' => $question,
                'answer' => $answer,
                'category' => 'General',
                'sort_order' => $order + 1,
                'is_published' => true,
            ]);
        }
    }

    protected function seedDemoUsers(): void
    {
        $users = [
            ['buyer.admin@vmi.test', 'Buyer Admin User', 'Buyer Admin', $this->buyer],
            ['buyer.manager@vmi.test', 'VMI Manager', 'Inventory/VMI Manager', $this->buyer],
            ['buyer.warehouse@vmi.test', 'Buyer Warehouse Lead', 'Buyer Warehouse', $this->buyer],
            ['buyer.finance@vmi.test', 'Buyer Finance Analyst', 'Buyer Finance', $this->buyer],
            ['supplier.admin@vmi.test', 'Atlas Supplier Admin', 'Supplier Admin', $this->suppliers['atlas-supply']],
            ['supplier.planner@vmi.test', 'Atlas Supply Planner', 'Supplier Planner', $this->suppliers['atlas-supply']],
            ['supplier.warehouse@vmi.test', 'Atlas Warehouse Coordinator', 'Supplier Warehouse', $this->suppliers['atlas-supply']],
            ['auditor@vmi.test', 'Compliance Auditor', 'Auditor', $this->buyer],
        ];

        foreach ($users as [$email, $name, $role, $organization]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $this->demoPassword(),
                    'email_verified_at' => now(),
                    'current_tenant_id' => $this->tenant->id,
                    'current_organization_id' => $organization->id,
                ]
            );

            $user->syncRoles([$role]);

            OrganizationMembership::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'user_id' => $user->id,
                ],
                [
                    'role_slug' => Str::slug($role),
                    'is_primary' => true,
                    'status' => 'active',
                    'joined_at' => now(),
                ]
            );
        }
    }

    protected function seedAuditLogs(): void
    {
        $userId = User::query()->where('email', 'buyer.admin@vmi.test')->value('id');
        $product = $this->products[0] ?? null;

        if ($product === null) {
            return;
        }

        AuditLog::query()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $userId,
            'organization_id' => $this->buyer->id,
            'action' => 'product.updated',
            'auditable_type' => Product::class,
            'auditable_id' => $product->id,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'active'],
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subHours(6),
        ]);
    }
}
