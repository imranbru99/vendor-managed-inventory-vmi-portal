# Demo environment

## Commands

```bash
php artisan vmi:demo-seed
php artisan vmi:demo-seed --fresh
```

Runs `RolesAndPermissionsSeeder` then `DemoSeeder` (DatabaseSeeder uses the same order).

## Tenant: Northstar Retail Group

- Slug: `northstar-retail` (configurable via `VMI_DEMO_TENANT_SLUG`)
- Buyer organization + 3 supplier orgs (Atlas, Global Distribution, Prime Wholesale)
- Warehouses: CDC, NWH, SWH
- 50 products across 8 categories
- VMI agreement with 36 Atlas policies (mixed policy types)
- Inventory: low / healthy / overstock mix; 30 days of demand events
- Replenishment run, pending + approved proposals, sample PO → ship → receive
- Modules synced from registry; optional modules enabled for tenant
- CMS home page, 3 blog posts, 8 FAQs
- Subscription plans: Starter → Enterprise

## Users

All passwords: `config('vmi.demo.password')`.

| Email | Role |
|-------|------|
| platform@vmi.test | Platform Admin |
| buyer.admin@vmi.test | Buyer Admin |
| buyer.manager@vmi.test | Inventory/VMI Manager |
| buyer.warehouse@vmi.test | Buyer Warehouse |
| buyer.finance@vmi.test | Buyer Finance |
| supplier.admin@vmi.test | Supplier Admin |
| supplier.planner@vmi.test | Supplier Planner |
| supplier.warehouse@vmi.test | Supplier Warehouse |
| auditor@vmi.test | Auditor |

Each user has `current_tenant_id` and `current_organization_id` set.
