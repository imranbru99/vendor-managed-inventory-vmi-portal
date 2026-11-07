<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\Shipments\Pages\ManageShipments;
use App\Models\Shipment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 42;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                Select::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'id')
                    ->required(),
                TextInput::make('relationship_id')
                    ->required()
                    ->numeric(),
                TextInput::make('shipment_number')
                    ->required(),
                TextInput::make('carrier'),
                TextInput::make('tracking_number'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('dispatched_at'),
                DateTimePicker::make('eta'),
                DateTimePicker::make('delivered_at'),
                TextInput::make('shipping_cost')
                    ->numeric()
                    ->prefix('$'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('meta')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchaseOrder.id')
                    ->searchable(),
                TextColumn::make('relationship_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('shipment_number')
                    ->searchable(),
                TextColumn::make('carrier')
                    ->searchable(),
                TextColumn::make('tracking_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('dispatched_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('eta')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('shipping_cost')
                    ->money()
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
            'index' => ManageShipments::route('/'),
        ];
    }
}
