# Dependency audit

## Admin UI

**Filament 4** is the sole operational admin framework for this project (`app/Filament/*`, `/admin`).

**laravel-admin-panel** (and similar competing admin scaffolding packages) are **not** installed.

## Attendance hub

`imrandevbd/laravel-attendance-hub` is required for device/log schema migrations. The Filament v3 plugin from that package is **not** registered in this app; attendance is exposed via a native adapter/status page pattern only (see `SystemHealth` / attendance migrations).

## Packages pinned to commit (dev-main)

Composer pins these when only `dev-main` exists upstream:

| Package | Constraint style |
|---------|------------------|
| `imrandevbd/drutogoti` | `dev-main#6c5b4c9…` |
| `imrandevbd/laravel-ai-catalog` | `dev-main#07b5783…` |
| `imrandevbd/laravel-attendance-hub` | `dev-main#7bd5a7b…` |
| `imrandevbd/laravel-unicode-pdf` | `dev-main#3dc97d3…` |
| `imrandevbd/laravel-universal-slug` | `dev-main#9fdcf16…` |

This prevents non-reproducible `composer update` drift.

## Stability choices

- **laravel-ai-hub** uses a tagged release (`^1.7`).
- **filament/filament** `^4.0` aligns with Livewire 3 stack.
- Drutogoti defaults **off** (`DRUTOGOTI_ENABLED=false`) in production-oriented configs.

## Testing note

PHPUnit sets `DRUTOGOTI_ENABLED=false` to avoid request profiler collisions in feature tests.
