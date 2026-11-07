<?php

namespace App\Filament\Resources\Organizations;

use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\Organizations\Pages\ManageOrganizations;
use App\Models\Organization;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = Organization::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name'),
                Select::make('type')
                    ->options(OrganizationType::class)
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('legal_name'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('logo_path'),
                TextInput::make('tax_id'),
                TextInput::make('registration_number'),
                TextInput::make('country_code'),
                TextInput::make('currency_code')
                    ->required()
                    ->default('USD'),
                TextInput::make('timezone')
                    ->required()
                    ->default('UTC'),
                TextInput::make('address_line1'),
                TextInput::make('address_line2'),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('postal_code'),
                Select::make('status')
                    ->options(OrganizationStatus::class)
                    ->default('pending')
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
                TextColumn::make('type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('legal_name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('logo_path')
                    ->searchable(),
                TextColumn::make('tax_id')
                    ->searchable(),
                TextColumn::make('registration_number')
                    ->searchable(),
                TextColumn::make('country_code')
                    ->searchable(),
                TextColumn::make('currency_code')
                    ->searchable(),
                TextColumn::make('timezone')
                    ->searchable(),
                TextColumn::make('address_line1')
                    ->searchable(),
                TextColumn::make('address_line2')
                    ->searchable(),
                TextColumn::make('city')
                    ->searchable(),
                TextColumn::make('state')
                    ->searchable(),
                TextColumn::make('postal_code')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
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
            'index' => ManageOrganizations::route('/'),
        ];
    }
}
