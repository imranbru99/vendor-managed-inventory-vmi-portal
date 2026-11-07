<?php

namespace App\Filament\Resources\ReplenishmentRecommendations;

use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\ReplenishmentRecommendations\Pages\ManageReplenishmentRecommendations;
use App\Models\ReplenishmentRecommendation;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
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

class ReplenishmentRecommendationResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = ReplenishmentRecommendation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Intelligence';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                TextInput::make('run_id')
                    ->required()
                    ->numeric(),
                TextInput::make('policy_id')
                    ->required()
                    ->numeric(),
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                TextInput::make('relationship_id')
                    ->required()
                    ->numeric(),
                TextInput::make('inventory_position')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('average_daily_consumption')
                    ->numeric(),
                TextInput::make('safety_stock')
                    ->numeric(),
                TextInput::make('reorder_point')
                    ->numeric(),
                TextInput::make('target_stock')
                    ->numeric(),
                TextInput::make('recommended_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                DatePicker::make('expected_delivery_date'),
                TextInput::make('days_of_inventory')
                    ->numeric(),
                Textarea::make('explanation')
                    ->columnSpanFull(),
                DateTimePicker::make('data_as_of'),
                Toggle::make('is_stale')
                    ->required(),
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
                TextColumn::make('run_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('policy_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('relationship_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('inventory_position')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('average_daily_consumption')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('safety_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reorder_point')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('target_stock')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('recommended_qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('expected_delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_of_inventory')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('data_as_of')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_stale')
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
            'index' => ManageReplenishmentRecommendations::route('/'),
        ];
    }
}
