<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AchievementsRelationManager extends RelationManager
{
    protected static string $relationship = 'achievements';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('achievementDefinition.name')->label('Achievement'),
                Tables\Columns\TextColumn::make('achievementDefinition.game.name')->label('Game'),
                Tables\Columns\TextColumn::make('unlocked_at')->dateTime()->sortable(),
            ])
            ->defaultSort('unlocked_at', 'desc');
    }
}
