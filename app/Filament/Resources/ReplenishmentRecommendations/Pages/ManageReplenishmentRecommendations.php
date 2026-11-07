<?php

namespace App\Filament\Resources\ReplenishmentRecommendations\Pages;

use App\Filament\Resources\ReplenishmentRecommendations\ReplenishmentRecommendationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageReplenishmentRecommendations extends ManageRecords
{
    protected static string $resource = ReplenishmentRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
