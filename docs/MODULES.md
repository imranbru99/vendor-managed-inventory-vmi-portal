# Modules

Definitions are centralized in `app/Services/Modules/ModuleRegistry.php`.

## Behavior

- **Required** modules (`core`, `multi-tenancy`, `users`) are always considered enabled.
- Optional modules are stored in `modules` + `tenant_modules` (`is_enabled`).
- **ModuleManager::enable()** validates dependencies and persists activation.
- **EnsureModuleEnabled** middleware aborts with 403 when a route requires a disabled module.

## Admin

Filament **Module Manager** (`app/Filament/Pages/ModuleManager.php`) toggles modules per tenant.

## Demo seeding

`DemoSeeder` syncs all registry entries into `modules` and enables optional modules for the buyer tenant in dependency order (multi-pass).

## API gating

Example: product endpoints require `module.enabled:catalog`; purchase orders require `procurement`.
