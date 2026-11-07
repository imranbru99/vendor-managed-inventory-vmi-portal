# Deployment

## Build

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build
```

## Database

Run migrations on deploy; use `php artisan vmi:demo-seed` only on non-production demo/staging environments.

## Process manager

- **PHP-FPM** or **Octane** for HTTP
- **queue worker** for async jobs (`QUEUE_CONNECTION=redis` recommended)
- **cron:** `* * * * * php /path/to/artisan schedule:run`

## Scheduler tasks

Registered in `routes/console.php`:

- `vmi:replenishment-evaluate`
- `vmi:low-stock-detect`
- `vmi:overdue-po-monitor`

Each writes to `scheduled_task_runs`.

## Health

Laravel health route: `/up`

Filament **System Health** page surfaces application checks.

## Secrets

Store `APP_KEY`, database credentials, and `DEMO_PASSWORD` in environment secrets—never commit `.env`.
