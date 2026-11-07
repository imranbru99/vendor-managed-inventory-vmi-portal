<?php

namespace App\Filament\Widgets;

use App\Models\BuyerSupplierRelationship;
use App\Models\PurchaseOrder;
use App\Support\TenantContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class SupplierPerformanceWidget extends TableWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenantId = TenantContext::getInstance()->getTenantId();

        return $table
            ->heading('Supplier performance (PO volume)')
            ->query(fn (): Builder => BuyerSupplierRelationship::query()
                ->with('supplierOrganization')
                ->when($tenantId, function (Builder $query) use ($tenantId): void {
                    $query->whereHas('buyerOrganization', fn ($q) => $q->where('tenant_id', $tenantId));
                })
                ->withCount('purchaseOrders')
                ->orderByDesc('purchase_orders_count')
                ->limit(8))
            ->columns([
                TextColumn::make('supplierOrganization.name')->label('Supplier'),
                TextColumn::make('status')->badge(),
                TextColumn::make('purchase_orders_count')->label('Purchase orders')->numeric(),
                TextColumn::make('ack_rate')
                    ->label('Acknowledged %')
                    ->state(function (BuyerSupplierRelationship $record) use ($tenantId): string {
                        $total = PurchaseOrder::query()
                            ->where('relationship_id', $record->id)
                            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                            ->count();

                        if ($total === 0) {
                            return '—';
                        }

                        $acked = PurchaseOrder::query()
                            ->where('relationship_id', $record->id)
                            ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                            ->whereNotNull('acknowledged_at')
                            ->count();

                        return (string) round(($acked / $total) * 100).'%';
                    }),
            ])
            ->paginated(false);
    }
}
