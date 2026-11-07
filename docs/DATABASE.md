# Database

Migrations live under `database/migrations/`.

## Core (`2026_11_08_000001_create_vmi_core_tables.php`)

- **tenants**, **organizations**, **organization_memberships**
- **buyer_supplier_relationships**
- **modules**, **tenant_modules**
- **subscription_plans**, **tenant_subscriptions**
- **warehouses**, **warehouse_locations**
- **product_categories**, **units_of_measure**, **products**, **supplier_products**
- **inventory_balances**, **inventory_transactions**
- **vmi_agreements**, **vmi_policies**
- **audit_logs**, **documents**

## Operations (`2026_11_08_000010_create_vmi_operations_tables.php`)

- **demand_events**, **replenishment_runs**, **replenishment_recommendations**
- **replenishment_proposals**, **replenishment_proposal_lines**
- **purchase_orders**, **purchase_order_lines**, **purchase_order_approvals**
- **shipments**, **shipment_lines**, **goods_receipts**, **goods_receipt_lines**
- **cms_pages**, **blog_posts**, **faqs**
- **scheduled_task_runs**

## Auth & platform

- Laravel users + `2026_11_08_000002_extend_users_table.php` (tenant/org context, platform admin)
- Spatie **permission** tables
- Sanctum **personal_access_tokens**

## Demo data

`DemoSeeder` populates Northstar Retail Group with 50 SKUs, 3 suppliers, 3 warehouses, policies, balances, demand history, sample proposals/POs, and CMS content.
