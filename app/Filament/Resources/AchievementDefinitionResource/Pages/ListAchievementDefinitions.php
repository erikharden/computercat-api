<?php

namespace App\Filament\Resources\AchievementDefinitionResource\Pages;

use App\Filament\Resources\AchievementDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAchievementDefinitions extends ListRecords
{
    protected static string $resource = AchievementDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
