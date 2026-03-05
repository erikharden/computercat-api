<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GameSavesRelationManager extends RelationManager
{
    protected static string $relationship = 'gameSaves';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')->label('Game'),
                Tables\Columns\TextColumn::make('save_key'),
                Tables\Columns\TextColumn::make('version'),
                Tables\Columns\TextColumn::make('saved_at')->dateTime()->sortable(),
            ])
            ->defaultSort('saved_at', 'desc');
    }
}
