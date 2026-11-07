<?php

namespace App\Filament\Resources\Modules;

use App\Enums\ModuleCategory;
use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\Modules\Pages\ManageModules;
use App\Models\Module;
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

class ModuleResource extends Resource
{
    use AdminResourceAccess;

    public static function canAccess(): bool
    {
        return static::platformAdminCanAccess();
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    protected static ?string $model = Module::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Platform Administration';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('icon'),
                Select::make('category')
                    ->options(ModuleCategory::class)
                    ->default('core')
                    ->required(),
                TextInput::make('version')
                    ->required()
                    ->default('1.0.0'),
                Toggle::make('is_required')
                    ->required(),
                Toggle::make('is_global_active')
                    ->required(),
                Textarea::make('dependencies')
                    ->columnSpanFull(),
                Textarea::make('permissions')
                    ->columnSpanFull(),
                Textarea::make('settings_schema')
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                TextColumn::make('category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('version')
                    ->searchable(),
                IconColumn::make('is_required')
                    ->boolean(),
                IconColumn::make('is_global_active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
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
            'index' => ManageModules::route('/'),
        ];
    }
}
