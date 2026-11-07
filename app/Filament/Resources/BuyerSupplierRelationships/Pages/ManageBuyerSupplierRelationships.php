<?php

namespace App\Filament\Resources\BuyerSupplierRelationships\Pages;

use App\Filament\Resources\BuyerSupplierRelationships\BuyerSupplierRelationshipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBuyerSupplierRelationships extends ManageRecords
{
    protected static string $resource = BuyerSupplierRelationshipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
