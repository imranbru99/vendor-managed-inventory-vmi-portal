<?php

namespace App\Filament\Resources\InventoryBalances\Pages;

use App\Filament\Resources\InventoryBalances\InventoryBalanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageInventoryBalances extends ManageRecords
{
    protected static string $resource = InventoryBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
