# Architecture

## Layers

| Layer | Location | Responsibility |
|-------|----------|----------------|
| HTTP (web) | `routes/web.php`, `app/Http/Controllers/PublicSiteController.php` | Marketing / CMS-backed public pages |
| HTTP (API) | `routes/api.php`, `app/Http/Controllers/Api/V1/*` | Token-authenticated JSON API |
| Admin UI | `app/Filament/*`, `app/Providers/Filament/AdminPanelProvider.php` | Operational CRUD, dashboards, module manager |
| Domain services | `app/Services/*` | Replenishment, procurement, inventory, logistics, receiving, audit |
| Tenancy | `App\Support\TenantContext`, `BelongsToTenant` trait | Request-scoped tenant filtering |
| Modules | `ModuleRegistry`, `ModuleManager`, `EnsureModuleEnabled` middleware | Feature flags per tenant |

## Request flow (API)

1. Sanctum authenticates the user.
2. `SetTenantContext` loads `current_tenant_id` / `current_organization_id` into `TenantContext`.
3. Optional `module.enabled:{slug}` gate blocks disabled features.
4. Eloquent global scopes on tenant models restrict queries.

## Key services

- **ReplenishmentEngine** — policy evaluation, ADC from `demand_events`, MOQ/multiple constraints.
- **ProposalService** — draft → submit → approve → PO generation.
- **PurchaseOrderService** — issue, acknowledge, fulfillment updates.
- **ShipmentService** / **GoodsReceiptService** — outbound and inbound with inventory posting.
- **DocumentPdfService** — PO PDF stream via UnicodePdf or HTML fallback.

## Packages

Primary UI is **Filament**; permissions via **Spatie Laravel Permission**; API tokens via **Sanctum**. See [DEPENDENCY-AUDIT.md](DEPENDENCY-AUDIT.md).
