<?php

namespace App\Filament\Resources\ReplenishmentProposals;

use App\Enums\ProposalStatus;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\ReplenishmentProposals\Pages\ManageReplenishmentProposals;
use App\Models\ReplenishmentProposal;
use App\Services\Replenishment\ProposalService;
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

class ReplenishmentProposalResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = ReplenishmentProposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 40;

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
                TextInput::make('proposal_number')
                    ->required(),
                Select::make('status')
                    ->options(ProposalStatus::class)
                    ->default('draft')
                    ->required(),
                TextInput::make('submitted_by')
                    ->numeric(),
                DateTimePicker::make('submitted_at'),
                TextInput::make('reviewed_by')
                    ->numeric(),
                DateTimePicker::make('reviewed_at'),
                Textarea::make('review_notes')
                    ->columnSpanFull(),
                TextInput::make('total_qty')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_value')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('currency_code')
                    ->required()
                    ->default('USD'),
                DatePicker::make('requested_delivery_date'),
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
                TextColumn::make('proposal_number')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('submitted_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('reviewed_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('total_qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_value')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('currency_code')
                    ->searchable(),
                TextColumn::make('requested_delivery_date')
                    ->date()
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
                Action::make('submit')
                    ->visible(fn ($record): bool => $record->status === ProposalStatus::Draft)
                    ->action(function ($record, ProposalService $service): void {
                        $service->submit($record);
                        Notification::make()->title('Proposal submitted')->success()->send();
                    }),
                Action::make('approve')
                    ->color('success')
                    ->visible(fn ($record): bool => $record->status === ProposalStatus::PendingApproval)
                    ->requiresConfirmation()
                    ->action(function ($record, ProposalService $service): void {
                        $record->loadMissing('lines');
                        $service->approve($record);
                        Notification::make()->title('Proposal approved')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->status === ProposalStatus::PendingApproval)
                    ->requiresConfirmation()
                    ->action(function ($record, ProposalService $service): void {
                        $service->reject($record);
                        Notification::make()->title('Proposal rejected')->success()->send();
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
            'index' => ManageReplenishmentProposals::route('/'),
        ];
    }
}
