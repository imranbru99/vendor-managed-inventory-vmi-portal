<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\VmiServiceProvider;

return [
    AppServiceProvider::class,
    VmiServiceProvider::class,
    AdminPanelProvider::class,
];
