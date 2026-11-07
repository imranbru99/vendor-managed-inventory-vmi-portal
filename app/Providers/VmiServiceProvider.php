<?php

namespace App\Providers;

use App\Http\Middleware\EnsureModuleEnabled;
use App\Http\Middleware\SetTenantContext;
use App\Services\Audit\AuditLogger;
use App\Services\Inventory\InventoryService;
use App\Services\Logistics\ShipmentService;
use App\Services\Modules\ModuleManager;
use App\Services\Modules\ModuleRegistry;
use App\Services\Procurement\PurchaseOrderService;
use App\Services\Receiving\GoodsReceiptService;
use App\Services\Replenishment\ProposalService;
use App\Services\Replenishment\ReplenishmentEngine;
use App\Services\Suppliers\SupplierRelationshipService;
use App\Services\Workflow\WorkflowService;
use App\Support\TenantContext;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class VmiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/vmi.php',
            'vmi'
        );

        $this->app->singleton(TenantContext::class, fn (Application $app): TenantContext => new TenantContext);

        $this->app->singleton(ModuleRegistry::class);
        $this->app->singleton(ModuleManager::class);

        $this->app->singleton(AuditLogger::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(ReplenishmentEngine::class);
        $this->app->singleton(PurchaseOrderService::class);
        $this->app->singleton(ProposalService::class);
        $this->app->singleton(ShipmentService::class);
        $this->app->singleton(GoodsReceiptService::class);
        $this->app->singleton(WorkflowService::class);
        $this->app->singleton(SupplierRelationshipService::class);
    }

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('tenant.context', SetTenantContext::class);
        $router->aliasMiddleware('module.enabled', EnsureModuleEnabled::class);
    }
}
