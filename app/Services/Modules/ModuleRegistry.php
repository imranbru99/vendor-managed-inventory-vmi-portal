<?php

namespace App\Services\Modules;

use App\Enums\ModuleCategory;

class ModuleRegistry
{
    /**
     * @return array<string, array{
     *     name: string,
     *     description: string,
     *     category: ModuleCategory,
     *     icon: string,
     *     is_required: bool,
     *     dependencies: list<string>,
     *     sort_order: int
     * }>
     */
    public function all(): array
    {
        return [
            'core' => [
                'name' => 'Core',
                'description' => 'Foundation platform services and shared utilities.',
                'category' => ModuleCategory::Core,
                'icon' => 'heroicon-o-cog-6-tooth',
                'is_required' => true,
                'dependencies' => [],
                'sort_order' => 10,
            ],
            'multi-tenancy' => [
                'name' => 'Multi-Tenancy',
                'description' => 'Tenant isolation, organizations, and tenant settings.',
                'category' => ModuleCategory::Core,
                'icon' => 'heroicon-o-building-office-2',
                'is_required' => true,
                'dependencies' => ['core'],
                'sort_order' => 20,
            ],
            'users' => [
                'name' => 'Users & Access',
                'description' => 'User management, roles, and organization memberships.',
                'category' => ModuleCategory::Core,
                'icon' => 'heroicon-o-users',
                'is_required' => true,
                'dependencies' => ['core', 'multi-tenancy'],
                'sort_order' => 30,
            ],
            'suppliers' => [
                'name' => 'Supplier Network',
                'description' => 'Buyer-supplier relationships and onboarding.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-truck',
                'is_required' => false,
                'dependencies' => ['multi-tenancy', 'users'],
                'sort_order' => 40,
            ],
            'catalog' => [
                'name' => 'Product Catalog',
                'description' => 'Products, categories, and units of measure.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-rectangle-stack',
                'is_required' => false,
                'dependencies' => ['multi-tenancy'],
                'sort_order' => 50,
            ],
            'inventory' => [
                'name' => 'Inventory',
                'description' => 'Stock balances, movements, and availability.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-cube',
                'is_required' => false,
                'dependencies' => ['catalog', 'warehouse'],
                'sort_order' => 60,
            ],
            'vmi' => [
                'name' => 'VMI Agreements',
                'description' => 'Vendor managed inventory agreements and policies.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-document-text',
                'is_required' => false,
                'dependencies' => ['suppliers', 'inventory'],
                'sort_order' => 70,
            ],
            'replenishment' => [
                'name' => 'Replenishment',
                'description' => 'Automated replenishment proposals and approvals.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-arrow-path',
                'is_required' => false,
                'dependencies' => ['vmi', 'inventory'],
                'sort_order' => 80,
            ],
            'procurement' => [
                'name' => 'Procurement',
                'description' => 'Purchase orders and supplier ordering.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-shopping-cart',
                'is_required' => false,
                'dependencies' => ['suppliers', 'catalog'],
                'sort_order' => 90,
            ],
            'warehouse' => [
                'name' => 'Warehouse',
                'description' => 'Warehouses, locations, and bin management.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-building-storefront',
                'is_required' => false,
                'dependencies' => ['multi-tenancy'],
                'sort_order' => 55,
            ],
            'receiving' => [
                'name' => 'Receiving',
                'description' => 'Goods receipt and inbound processing.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-inbox-arrow-down',
                'is_required' => false,
                'dependencies' => ['warehouse', 'procurement'],
                'sort_order' => 100,
            ],
            'logistics' => [
                'name' => 'Logistics',
                'description' => 'Shipments, carriers, and delivery tracking.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-map',
                'is_required' => false,
                'dependencies' => ['warehouse', 'procurement'],
                'sort_order' => 110,
            ],
            'consignment' => [
                'name' => 'Consignment',
                'description' => 'Consignment stock and ownership transfer.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-hand-raised',
                'is_required' => false,
                'dependencies' => ['inventory', 'vmi'],
                'sort_order' => 120,
            ],
            'quality' => [
                'name' => 'Quality',
                'description' => 'Inspections, quarantine, and quality holds.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-shield-check',
                'is_required' => false,
                'dependencies' => ['inventory', 'receiving'],
                'sort_order' => 130,
            ],
            'lot-expiry' => [
                'name' => 'Lot & Expiry',
                'description' => 'Batch tracking, expiry, and FEFO.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-calendar-days',
                'is_required' => false,
                'dependencies' => ['inventory'],
                'sort_order' => 140,
            ],
            'returns' => [
                'name' => 'Returns',
                'description' => 'Return merchandise authorization and processing.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-arrow-uturn-left',
                'is_required' => false,
                'dependencies' => ['inventory', 'receiving'],
                'sort_order' => 150,
            ],
            'finance' => [
                'name' => 'Finance',
                'description' => 'Costing, invoicing, and financial reconciliation.',
                'category' => ModuleCategory::Commerce,
                'icon' => 'heroicon-o-banknotes',
                'is_required' => false,
                'dependencies' => ['procurement', 'inventory'],
                'sort_order' => 160,
            ],
            'forecasting' => [
                'name' => 'Forecasting',
                'description' => 'Demand planning and forecast models.',
                'category' => ModuleCategory::Analytics,
                'icon' => 'heroicon-o-chart-bar',
                'is_required' => false,
                'dependencies' => ['inventory', 'replenishment'],
                'sort_order' => 170,
            ],
            'ai' => [
                'name' => 'AI Assist',
                'description' => 'AI-powered insights, catalog, and automation.',
                'category' => ModuleCategory::Analytics,
                'icon' => 'heroicon-o-sparkles',
                'is_required' => false,
                'dependencies' => ['core'],
                'sort_order' => 180,
            ],
            'integrations' => [
                'name' => 'Integrations',
                'description' => 'ERP, EDI, and third-party connectors.',
                'category' => ModuleCategory::Integration,
                'icon' => 'heroicon-o-puzzle-piece',
                'is_required' => false,
                'dependencies' => ['core'],
                'sort_order' => 190,
            ],
            'notifications' => [
                'name' => 'Notifications',
                'description' => 'Email, in-app, and webhook notifications.',
                'category' => ModuleCategory::Support,
                'icon' => 'heroicon-o-bell',
                'is_required' => false,
                'dependencies' => ['core', 'users'],
                'sort_order' => 200,
            ],
            'reporting' => [
                'name' => 'Reporting',
                'description' => 'Dashboards, exports, and scheduled reports.',
                'category' => ModuleCategory::Analytics,
                'icon' => 'heroicon-o-presentation-chart-line',
                'is_required' => false,
                'dependencies' => ['inventory'],
                'sort_order' => 210,
            ],
            'workforce' => [
                'name' => 'Workforce',
                'description' => 'Tasks, assignments, and warehouse labor.',
                'category' => ModuleCategory::Operations,
                'icon' => 'heroicon-o-clipboard-document-list',
                'is_required' => false,
                'dependencies' => ['warehouse', 'users'],
                'sort_order' => 220,
            ],
            'documents' => [
                'name' => 'Documents',
                'description' => 'File storage and document attachments.',
                'category' => ModuleCategory::Support,
                'icon' => 'heroicon-o-folder',
                'is_required' => false,
                'dependencies' => ['core'],
                'sort_order' => 230,
            ],
            'cms' => [
                'name' => 'CMS',
                'description' => 'Content pages and portal customization.',
                'category' => ModuleCategory::Support,
                'icon' => 'heroicon-o-newspaper',
                'is_required' => false,
                'dependencies' => ['core'],
                'sort_order' => 240,
            ],
            'subscription' => [
                'name' => 'Subscription',
                'description' => 'Plans, billing, and tenant subscriptions.',
                'category' => ModuleCategory::Core,
                'icon' => 'heroicon-o-credit-card',
                'is_required' => false,
                'dependencies' => ['multi-tenancy'],
                'sort_order' => 250,
            ],
            'helpdesk' => [
                'name' => 'Helpdesk',
                'description' => 'Support tickets and knowledge base.',
                'category' => ModuleCategory::Support,
                'icon' => 'heroicon-o-lifebuoy',
                'is_required' => false,
                'dependencies' => ['users', 'notifications'],
                'sort_order' => 260,
            ],
        ];
    }

    public function get(string $slug): ?array
    {
        return $this->all()[$slug] ?? null;
    }

    /**
     * @return list<string>
     */
    public function dependencies(string $slug): array
    {
        return $this->get($slug)['dependencies'] ?? [];
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return array_keys($this->all());
    }
}
