<?php

namespace App\Filament\Widgets;

use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\VmiPolicy;
use App\Support\TenantContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LowStockProductsWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenantId = TenantContext::getInstance()->getTenantId();

        return $table
            ->heading('Low stock products')
            ->query(fn (): Builder => Product::query()
                ->whereIn('id', $this->lowStockProductIds($tenantId))
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId)))
            ->columns([
                TextColumn::make('sku'),
                TextColumn::make('name')->limit(40),
                TextColumn::make('status')->badge(),
            ])
            ->paginated(false);
    }

    /**
     * @return Collection<int, int>
     */
    protected function lowStockProductIds(?int $tenantId): Collection
    {
        $ids = collect();

        VmiPolicy::query()
            ->where('is_active', true)
            ->whereNotNull('min_stock')
            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
            ->get(['product_id', 'warehouse_id', 'min_stock'])
            ->each(function (VmiPolicy $policy) use ($tenantId, $ids): void {
                $sellable = (float) InventoryBalance::query()
                    ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                    ->where('product_id', $policy->product_id)
                    ->where('warehouse_id', $policy->warehouse_id)
                    ->sum('sellable');

                if ($sellable < (float) $policy->min_stock) {
                    $ids->push($policy->product_id);
                }
            });

        return $ids->unique()->values();
    }
}
