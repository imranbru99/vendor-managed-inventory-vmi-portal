# Integrations

## Implemented in this repository

| Package | Role |
|---------|------|
| `filament/filament` | Primary admin UI |
| `laravel/sanctum` | API tokens |
| `spatie/laravel-permission` | Roles & permissions |
| `imrandevbd/laravel-unicode-pdf` | PO PDF generation (`DocumentPdfService`) |
| `imrandevbd/laravel-ai-hub` | AI assistant page (optional feature flag) |
| `imrandevbd/laravel-ai-catalog` | AI catalog tooling |
| `imrandevbd/laravel-attendance-hub` | Attendance schema present; native status integration only |
| `imrandevbd/drutogoti` | Dev observability (disabled by default) |
| `imrandevbd/laravel-universal-slug` | Slug utilities |

Filament resources include **Integrations** for connector metadata (`Integration` model).

## EDI / ERP

Feature flag `VMI_FEATURE_EDI` exists in config; EDI connectors are not fully implemented—treat as roadmap.

## Webhooks

Schema includes `webhook_endpoints` and `webhook_deliveries` for future outbound events.
