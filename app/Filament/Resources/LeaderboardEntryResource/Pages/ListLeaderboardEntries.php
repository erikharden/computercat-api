<?php

namespace App\Filament\Resources\LeaderboardEntryResource\Pages;

use App\Filament\Resources\LeaderboardEntryResource;
use Filament\Resources\Pages\ListRecords;

class ListLeaderboardEntries extends ListRecords
{
    protected static string $resource = LeaderboardEntryResource::class;
}
