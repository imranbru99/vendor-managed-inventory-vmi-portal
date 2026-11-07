<?php

namespace App\Filament\Resources\Products;

use App\Filament\Concerns\AdminResourceAccess;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    use AdminResourceAccess;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|\UnitEnum|null $navigationGroup = 'Supply Chain';

    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('tenant_id')
                    ->relationship('tenant', 'name')
                    ->required(),
                Select::make('organization_id')
                    ->relationship('organization', 'name')
                    ->required(),
                Select::make('category_id')
                    ->relationship('category', 'name'),
                TextInput::make('sku')
                    ->label('SKU')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('barcode'),
                TextInput::make('gtin'),
                TextInput::make('brand'),
                TextInput::make('manufacturer'),
                Select::make('base_uom_id')
                    ->relationship('baseUom', 'id'),
                TextInput::make('case_quantity')
                    ->numeric(),
                TextInput::make('weight')
                    ->numeric(),
                TextInput::make('weight_unit'),
                TextInput::make('length')
                    ->numeric(),
                TextInput::make('width')
                    ->numeric(),
                TextInput::make('height')
                    ->numeric(),
                TextInput::make('dimension_unit'),
                TextInput::make('tax_category'),
                TextInput::make('status')
                    ->required()
                    ->default('active'),
                TextInput::make('shelf_life_days')
                    ->numeric(),
                Textarea::make('storage_requirements')
                    ->columnSpanFull(),
                Textarea::make('specifications')
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->image(),
                Textarea::make('meta')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tenant.name')
                    ->searchable(),
                TextColumn::make('organization.name')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('barcode')
                    ->searchable(),
                TextColumn::make('gtin')
                    ->searchable(),
                TextColumn::make('brand')
                    ->searchable(),
                TextColumn::make('manufacturer')
                    ->searchable(),
                TextColumn::make('baseUom.id')
                    ->searchable(),
                TextColumn::make('case_quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('weight_unit')
                    ->searchable(),
                TextColumn::make('length')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('width')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('height')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('dimension_unit')
                    ->searchable(),
                TextColumn::make('tax_category')
                    ->searchable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('shelf_life_days')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('image_path'),
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
            'index' => ManageProducts::route('/'),
        ];
    }
}
