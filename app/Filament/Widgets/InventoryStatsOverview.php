<?php

namespace App\Filament\Widgets;

use App\Models\InventoryBalance;
use App\Models\PurchaseOrder;
use App\Models\ReplenishmentProposal;
use App\Models\VmiPolicy;
use App\Support\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class InventoryStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenantId = TenantContext::getInstance()->getTenantId();

        $lowStock = $this->countLowStock($tenantId);
        $openPos = PurchaseOrder::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['issued', 'acknowledged', 'open', 'partially_received'])
            ->count();
        $pendingProposals = ReplenishmentProposal::query()
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->whereIn('status', ['draft', 'pending_approval'])
            ->count();

        return [
            Stat::make('Low stock SKUs', (string) $lowStock)
                ->description('Below VMI minimum')
                ->color($lowStock > 0 ? 'warning' : 'success'),
            Stat::make('Open purchase orders', (string) $openPos)
                ->description('Issued or in progress')
                ->color('info'),
            Stat::make('Pending proposals', (string) $pendingProposals)
                ->description('Awaiting review')
                ->color($pendingProposals > 0 ? 'warning' : 'gray'),
        ];
    }

    protected function countLowStock(?int $tenantId): int
    {
        $query = VmiPolicy::query()
            ->where('is_active', true)
            ->whereNotNull('min_stock')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId));

        $policies = $query->get(['id', 'product_id', 'warehouse_id', 'min_stock']);

        if ($policies->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($policies as $policy) {
            $sellable = (float) InventoryBalance::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->where('product_id', $policy->product_id)
                ->where('warehouse_id', $policy->warehouse_id)
                ->sum(DB::raw('sellable'));

            if ($sellable < (float) $policy->min_stock) {
                $count++;
            }
        }

        return $count;
    }
}
