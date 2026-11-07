<?php

namespace App\Filament\Resources\CmsPages;

use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\CmsPages\Pages\ManageCmsPages;
use App\Models\CmsPage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CmsPageResource extends Resource
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

    protected static ?string $model = CmsPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 61;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => ManageCmsPages::route('/'),
        ];
    }
}
