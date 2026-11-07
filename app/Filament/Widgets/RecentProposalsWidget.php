<?php

namespace App\Filament\Widgets;

use App\Models\ReplenishmentProposal;
use App\Support\TenantContext;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentProposalsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $tenantId = TenantContext::getInstance()->getTenantId();

        return $table
            ->heading('Recent replenishment proposals')
            ->query(fn (): Builder => ReplenishmentProposal::query()
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->latest()
                ->limit(10))
            ->columns([
                TextColumn::make('proposal_number')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('total_qty')->numeric(decimalPlaces: 2),
                TextColumn::make('total_value')->money(fn ($record) => $record->currency_code ?? 'USD'),
                TextColumn::make('submitted_at')->dateTime()->placeholder('—'),
            ])
            ->paginated(false);
    }
}
