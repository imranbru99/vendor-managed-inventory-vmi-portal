<?php

namespace App\Filament\Concerns;

trait AdminResourceAccess
{
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    protected static function requiresPlatformAdmin(): bool
    {
        return false;
    }

    public static function platformAdminCanAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && (bool) $user->is_platform_admin;
    }
}
