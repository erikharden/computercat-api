<?php

namespace App\Filament\Resources\AchievementDefinitionResource\Pages;

use App\Filament\Resources\AchievementDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAchievementDefinition extends EditRecord
{
    protected static string $resource = AchievementDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
