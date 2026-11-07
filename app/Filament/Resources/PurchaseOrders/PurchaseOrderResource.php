<?php

namespace App\Filament\Resources\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\PurchaseOrders\Pages\ManagePurchaseOrders;
use App\Models\PurchaseOrder;
use App\Services\Procurement\PurchaseOrderService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

class PurchaseOrderResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = PurchaseOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 41;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('tenant_id')
                    ->required()
                    ->numeric(),
                Select::make('relationship_id')
                    ->relationship('relationship', 'id')
                    ->required(),
                TextInput::make('proposal_id')
                    ->numeric(),
                TextInput::make('po_number')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('currency_code')
                    ->required()
                    ->default('USD'),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('tax_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('discount_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('grand_total')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('delivery_warehouse_id')
                    ->numeric(),
                DatePicker::make('required_delivery_date'),
                TextInput::make('payment_terms'),
                Textarea::make('terms')
                    ->columnSpanFull(),
                DateTimePicker::make('issued_at'),
                DateTimePicker::make('acknowledged_at'),
                DateTimePicker::make('closed_at'),
                TextInput::make('created_by')
                    ->numeric(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('idempotency_key'),
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
                TextColumn::make('relationship.id')
                    ->searchable(),
                TextColumn::make('proposal_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('po_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('currency_code')
                    ->searchable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('grand_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('delivery_warehouse_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('required_delivery_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('payment_terms')
                    ->searchable(),
                TextColumn::make('issued_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('acknowledged_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('idempotency_key')
                    ->searchable(),
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
                Action::make('issue')
                    ->visible(fn ($record): bool => $record->status === PurchaseOrderStatus::Draft)
                    ->action(function ($record, PurchaseOrderService $service): void {
                        $service->issue($record);
                        Notification::make()->title('Purchase order issued')->success()->send();
                    }),
                Action::make('acknowledge')
                    ->visible(fn ($record): bool => $record->status === PurchaseOrderStatus::Submitted)
                    ->action(function ($record, PurchaseOrderService $service): void {
                        $service->acknowledge($record);
                        Notification::make()->title('Purchase order acknowledged')->success()->send();
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
            'index' => ManagePurchaseOrders::route('/'),
        ];
    }
}
