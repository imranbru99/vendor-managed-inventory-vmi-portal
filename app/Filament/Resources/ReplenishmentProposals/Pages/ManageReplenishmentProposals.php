<?php

namespace App\Filament\Resources\ReplenishmentProposals\Pages;

use App\Filament\Resources\ReplenishmentProposals\ReplenishmentProposalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReplenishmentProposals extends ManageRecords
{
    protected static string $resource = ReplenishmentProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
