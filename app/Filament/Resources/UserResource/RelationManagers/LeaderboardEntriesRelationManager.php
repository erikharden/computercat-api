<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LeaderboardEntriesRelationManager extends RelationManager
{
    protected static string $relationship = 'leaderboardEntries';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaderboardType.game.name')->label('Game'),
                Tables\Columns\TextColumn::make('leaderboardType.name')->label('Leaderboard'),
                Tables\Columns\TextColumn::make('period_key'),
                Tables\Columns\TextColumn::make('score')->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc');
    }
}
