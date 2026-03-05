<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaderboardEntryResource\Pages;
use App\Models\LeaderboardEntry;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeaderboardEntryResource extends Resource
{
    protected static ?string $model = LeaderboardEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';

    protected static ?string $navigationGroup = 'Game Data';

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('leaderboardType.game.name')->label('Game')->sortable(),
                Tables\Columns\TextColumn::make('leaderboardType.name')->label('Leaderboard')->sortable(),
                Tables\Columns\TextColumn::make('user.display_name')
                    ->default(fn ($record) => $record->user->name)
                    ->label('Player')
                    ->searchable(['users.display_name', 'users.name']),
                Tables\Columns\TextColumn::make('period_key')->sortable(),
                Tables\Columns\TextColumn::make('score')->sortable(),
                Tables\Columns\TextColumn::make('submitted_at')->dateTime()->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('leaderboard_type_id')
                    ->relationship('leaderboardType', 'name')
                    ->label('Leaderboard'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLeaderboardEntries::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
