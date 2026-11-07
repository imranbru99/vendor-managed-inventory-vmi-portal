# API v1

Base URL: `/api/v1`

Authentication: **Bearer token** (Sanctum). Obtain via:

```http
POST /api/v1/auth/token
Content-Type: application/json

{"email":"buyer.admin@vmi.test","password":"password"}
```

Response includes `token` and user context IDs. Revoke: `POST /api/v1/auth/logout` (authenticated).

## Middleware

- `auth:sanctum`
- `tenant.context` — scopes data to `current_tenant_id`
- `throttle:120,1` on authenticated routes; login limited to `10,1`
- Per-route `module.enabled:{slug}`

## Endpoints

| Method | Path | Module | Description |
|--------|------|--------|-------------|
| GET | `/products` | catalog | Paginated products |
| GET | `/products/{id}` | catalog | Product detail |
| GET | `/inventory/balances` | inventory | Stock balances |
| GET | `/replenishment/proposals` | replenishment | Proposals list |
| GET | `/replenishment/proposals/{id}` | replenishment | Proposal detail |
| GET | `/purchase-orders` | procurement | PO list (supplier-scoped) |
| GET | `/purchase-orders/{id}` | procurement | PO detail (supplier access check) |

## Supplier scoping

Suppliers only see purchase orders for relationships where they are the `supplier_organization_id`.

## OpenAPI

Full OpenAPI specification is not yet generated; this document reflects the implemented surface. Use Filament for write operations until mutation endpoints are added.
