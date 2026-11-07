<?php

namespace App\Filament\Resources\VmiPolicies\Pages;

use App\Filament\Resources\VmiPolicies\VmiPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageVmiPolicies extends ManageRecords
{
    protected static string $resource = VmiPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
