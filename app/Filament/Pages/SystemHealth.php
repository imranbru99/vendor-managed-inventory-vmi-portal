<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SystemHealth extends Page
{
    protected string $view = 'filament.pages.system-health';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform Administration';

    protected static ?int $navigationSort = 99;

    public bool $databaseOk = false;

    public bool $cacheOk = false;

    public string $queueConnection = '';

    public ?string $databaseError = null;

    public ?string $cacheError = null;

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_platform_admin;
    }

    public function mount(): void
    {
        $this->queueConnection = (string) config('queue.default');

        try {
            DB::connection()->getPdo();
            $this->databaseOk = true;
        } catch (\Throwable $e) {
            $this->databaseError = $e->getMessage();
        }

        try {
            $key = 'filament_health_'.uniqid();
            Cache::put($key, 'ok', 10);
            $this->cacheOk = Cache::get($key) === 'ok';
            Cache::forget($key);
        } catch (\Throwable $e) {
            $this->cacheError = $e->getMessage();
        }
    }

    public function queueDriverLabel(): string
    {
        return match ($this->queueConnection) {
            'sync' => 'Sync (inline)',
            'database' => 'Database',
            'redis' => 'Redis',
            default => $this->queueConnection !== '' ? $this->queueConnection : 'Unknown',
        };
    }

    public function queueIsAsync(): bool
    {
        return $this->queueConnection !== 'sync';
    }
}
