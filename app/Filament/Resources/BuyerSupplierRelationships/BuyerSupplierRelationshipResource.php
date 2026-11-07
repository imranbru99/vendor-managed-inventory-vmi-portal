<?php

namespace App\Filament\Resources\BuyerSupplierRelationships;

use App\Enums\RelationshipStatus;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\BuyerSupplierRelationships\Pages\ManageBuyerSupplierRelationships;
use App\Models\BuyerSupplierRelationship;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BuyerSupplierRelationshipResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = BuyerSupplierRelationship::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|\UnitEnum|null $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('buyer_organization_id')
                    ->relationship('buyerOrganization', 'name')
                    ->required(),
                Select::make('supplier_organization_id')
                    ->relationship('supplierOrganization', 'name')
                    ->required(),
                Select::make('status')
                    ->options(RelationshipStatus::class)
                    ->default('pending')
                    ->required(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('suspended_at'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('settings')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('buyerOrganization.name')
                    ->searchable(),
                TextColumn::make('supplierOrganization.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('suspended_at')
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
                Action::make('approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === RelationshipStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update([
                            'status' => RelationshipStatus::Approved,
                            'approved_at' => now(),
                        ]);
                        Notification::make()->title('Relationship approved')->success()->send();
                    }),
                Action::make('reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->status === RelationshipStatus::Pending)
                    ->requiresConfirmation()
                    ->action(function ($record): void {
                        $record->update([
                            'status' => RelationshipStatus::Terminated,
                            'suspended_at' => now(),
                        ]);
                        Notification::make()->title('Relationship rejected')->success()->send();
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
            'index' => ManageBuyerSupplierRelationships::route('/'),
        ];
    }
}
