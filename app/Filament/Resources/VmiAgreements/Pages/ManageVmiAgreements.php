<?php

namespace App\Filament\Resources\VmiAgreements\Pages;

use App\Filament\Resources\VmiAgreements\VmiAgreementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVmiAgreements extends ManageRecords
{
    protected static string $resource = VmiAgreementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
