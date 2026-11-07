<?php

namespace App\Filament\Resources\VmiPolicies;

use App\Enums\ReplenishmentPolicyType;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\VmiPolicies\Pages\ManageVmiPolicies;
use App\Models\VmiPolicy;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VmiPolicyResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = VmiPolicy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|\UnitEnum|null $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 14;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('agreement_id')
                    ->relationship('agreement', 'name')
                    ->required(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                Select::make('policy_type')
                    ->options(ReplenishmentPolicyType::class)
                    ->required(),
                TextInput::make('min_stock')
                    ->numeric(),
                TextInput::make('max_stock')
                    ->numeric(),
                TextInput::make('safety_stock')
                    ->numeric(),
                TextInput::make('reorder_point')
                    ->numeric(),
                TextInput::make('target_days_of_supply')
                    ->numeric(),
                TextInput::make('replenishment_frequency_days')
                    ->numeric(),
                TextInput::make('lead_time_days')
                    ->numeric(),
                TextInput::make('service_level_target')
                    ->numeric(),
                TextInput::make('moq')
                    ->numeric(),
                TextInput::make('order_multiple')
                    ->numeric(),
                TextInput::make('max_order_qty')
                    ->numeric(),
                Toggle::make('requires_approval')
                    ->required(),
                TextInput::make('auto_approve_max_qty')
                    ->numeric(),
                TextInput::make('auto_approve_max_value')
                    ->numeric(),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('settings')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->searchable(),
                TextColumn::make('agreement.name')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('policy_type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('min_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('safety_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_point')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_days_of_supply')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('replenishment_frequency_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lead_time_days')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('service_level_target')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('moq')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('order_multiple')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('max_order_qty')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('requires_approval')
                    ->boolean(),
                TextColumn::make('auto_approve_max_qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('auto_approve_max_value')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageVmiPolicies::route('/'),
        ];
    }
}
