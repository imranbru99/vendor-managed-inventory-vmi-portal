<?php

namespace App\Filament\Resources\InventoryBalances;

use App\Enums\InventoryTransactionType;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\InventoryBalances\Pages\ManageInventoryBalances;
use App\Models\InventoryBalance;
use App\Services\Inventory\InventoryService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InventoryBalanceResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = InventoryBalance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Warehouse';

    protected static ?int $navigationSort = 21;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                Select::make('location_id')
                    ->relationship('location', 'name'),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                TextInput::make('on_hand')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('sellable')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reserved')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('allocated')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('quarantined')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('damaged')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('in_transit')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('confirmed_inbound')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('consignment')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('returned')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('average_cost')
                    ->numeric()
                    ->prefix('$'),
                DateTimePicker::make('last_counted_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('location.name')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('on_hand')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('sellable')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reserved')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('allocated')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('quarantined')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('damaged')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('in_transit')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('confirmed_inbound')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('consignment')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('returned')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('average_cost')
                    ->money()
                    ->sortable(),
                TextColumn::make('last_counted_at')
                    ->dateTime()
                    ->sortable(),
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
                Action::make('adjust')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        TextInput::make('quantity')
                            ->numeric()
                            ->required()
                            ->helperText('Positive increases sellable stock; negative decreases.'),
                        TextInput::make('notes'),
                    ])
                    ->action(function ($record, array $data, InventoryService $inventoryService): void {
                        $inventoryService->adjustStock(
                            $record->product,
                            $record->warehouse,
                            (float) $data['quantity'],
                            InventoryTransactionType::Adjustment,
                            ['notes' => $data['notes'] ?? null],
                            $record->location_id,
                        );
                        Notification::make()->title('Inventory adjusted')->success()->send();
                    }),
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
            'index' => ManageInventoryBalances::route('/'),
        ];
    }
}
