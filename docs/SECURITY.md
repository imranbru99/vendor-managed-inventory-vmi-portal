# Security

## Authentication

- **Filament admin:** session guard (`web`).
- **API:** Laravel Sanctum personal access tokens (`POST /api/v1/auth/token`).

## Authorization

- **Spatie Permission** roles seeded in `RolesAndPermissionsSeeder` (Buyer Admin, Supplier Admin, Auditor, etc.).
- Platform admin flag: `users.is_platform_admin` for cross-tenant Filament access (see Filament middleware).

## Tenant isolation

- `TenantContext` set per API/admin request from the user's `current_tenant_id`.
- `BelongsToTenant` global scope on tenant-owned models prevents cross-tenant reads when context is set.

## Module isolation

Disabled modules return HTTP 403 from `EnsureModuleEnabled`.

## Supplier isolation

Purchase order API enforces supplier organization membership on the underlying relationship.

## Recommendations

- Set strong `DEMO_PASSWORD` in production; never ship default demo users on public deployments.
- Use HTTPS, rotate Sanctum tokens, and restrict Filament to trusted networks or SSO when available.
