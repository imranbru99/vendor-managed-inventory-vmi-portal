<?php

namespace App\Filament\Resources\GoodsReceipts;

use App\Enums\GoodsReceiptStatus;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\GoodsReceipts\Pages\ManageGoodsReceipts;
use App\Models\GoodsReceipt;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoodsReceiptResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = GoodsReceipt::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 43;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                Select::make('warehouse_id')
                    ->relationship('warehouse', 'name')
                    ->required(),
                Select::make('purchase_order_id')
                    ->relationship('purchaseOrder', 'id')
                    ->required(),
                TextInput::make('shipment_id')
                    ->numeric(),
                TextInput::make('grn_number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('received_by')
                    ->numeric(),
                DateTimePicker::make('received_at'),
                Textarea::make('notes')
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
                TextColumn::make('warehouse.name')
                    ->searchable(),
                TextColumn::make('purchaseOrder.id')
                    ->searchable(),
                TextColumn::make('shipment_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('grn_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('received_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('received_at')
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
                Action::make('receive')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record): bool => in_array($record->status, ['draft', 'pending', 'in_progress'], true))
                    ->action(function ($record): void {
                        $record->update([
                            'status' => GoodsReceiptStatus::Completed,
                            'received_at' => now(),
                            'received_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Goods receipt completed')->success()->send();
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
            'index' => ManageGoodsReceipts::route('/'),
        ];
    }
}
