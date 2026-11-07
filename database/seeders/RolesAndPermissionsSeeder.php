<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    public const PERMISSIONS = [
        'platform.admin',
        'modules.manage',
        'users.manage',
        'organizations.manage',
        'suppliers.view',
        'suppliers.manage',
        'catalog.view',
        'catalog.manage',
        'inventory.view',
        'inventory.manage',
        'warehouse.operate',
        'vmi.view',
        'vmi.manage',
        'replenishment.view',
        'replenishment.manage',
        'replenishment.approve',
        'procurement.view',
        'procurement.manage',
        'procurement.acknowledge',
        'logistics.view',
        'logistics.manage',
        'receiving.operate',
        'finance.view',
        'finance.manage',
        'reporting.view',
        'audit.view',
        'cms.manage',
        'integrations.manage',
        'api.access',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $all = Permission::query()->pluck('name')->all();

        $roles = [
            'Platform Admin' => $all,
            'Buyer Admin' => [
                'users.manage', 'organizations.manage', 'suppliers.view', 'suppliers.manage',
                'catalog.view', 'catalog.manage', 'inventory.view', 'inventory.manage',
                'warehouse.operate', 'vmi.view', 'vmi.manage', 'replenishment.view',
                'replenishment.manage', 'replenishment.approve', 'procurement.view',
                'procurement.manage', 'logistics.view', 'receiving.operate', 'finance.view',
                'finance.manage', 'reporting.view', 'audit.view', 'api.access',
            ],
            'Inventory/VMI Manager' => [
                'catalog.view', 'inventory.view', 'inventory.manage', 'warehouse.operate',
                'vmi.view', 'vmi.manage', 'replenishment.view', 'replenishment.manage',
                'replenishment.approve', 'procurement.view', 'reporting.view', 'api.access',
            ],
            'Buyer Warehouse' => [
                'catalog.view', 'inventory.view', 'warehouse.operate', 'receiving.operate',
                'logistics.view', 'api.access',
            ],
            'Buyer Finance' => [
                'procurement.view', 'finance.view', 'finance.manage', 'reporting.view', 'audit.view', 'api.access',
            ],
            'Supplier Admin' => [
                'suppliers.view', 'catalog.view', 'replenishment.view', 'procurement.view',
                'procurement.manage', 'procurement.acknowledge', 'logistics.view', 'logistics.manage', 'api.access',
            ],
            'Supplier Planner' => [
                'catalog.view', 'replenishment.view', 'replenishment.manage', 'procurement.view', 'api.access',
            ],
            'Supplier Warehouse' => [
                'catalog.view', 'logistics.view', 'logistics.manage', 'procurement.view', 'api.access',
            ],
            'Auditor' => [
                'catalog.view', 'inventory.view', 'vmi.view', 'replenishment.view', 'procurement.view',
                'logistics.view', 'finance.view', 'reporting.view', 'audit.view', 'api.access',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
