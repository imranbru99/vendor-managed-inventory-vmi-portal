# Installation

## Requirements

- PHP 8.2+
- Composer
- SQLite, MySQL, or PostgreSQL
- Node.js (for Vite asset build if customizing Filament/theme assets)

## Steps

```bash
git clone <repo>
cd vendor-managed-inventory-portal-supplier-controlled-replenishment
composer install
cp .env.example .env
php artisan key:generate
```

Configure database in `.env`, then:

```bash
php artisan migrate:fresh --seed --force
npm install && npm run build   # optional front-end build
php artisan serve
```

Filament panel: `/admin` (create a platform admin or use `platform@vmi.test` after seeding).

## Environment keys

| Variable | Purpose |
|----------|---------|
| `DEMO_PASSWORD` | Demo user password (see `config/vmi.php`) |
| `DRUTOGOTI_ENABLED` | Set `false` in tests/CI (default false) |
| `VMI_FEATURE_*` | Feature toggles in `config/vmi.php` |

## Queue & scheduler

For queued jobs and scheduled replenishment:

```bash
php artisan queue:work
php artisan schedule:work   # or system cron → schedule:run
```
