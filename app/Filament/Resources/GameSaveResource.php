<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameSaveResource\Pages;
use App\Models\GameSave;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameSaveResource extends Resource
{
    protected static ?string $model = GameSave::class;

    protected static ?string $navigationIcon = 'heroicon-o-cloud-arrow-up';

    protected static ?string $navigationGroup = 'Game Data';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.display_name')
                    ->default(fn ($record) => $record->user->name)
                    ->label('Player')
                    ->searchable(['users.display_name', 'users.name']),
                Tables\Columns\TextColumn::make('game.name')->sortable(),
                Tables\Columns\TextColumn::make('save_key'),
                Tables\Columns\TextColumn::make('version'),
                Tables\Columns\TextColumn::make('saved_at')->dateTime()->sortable(),
            ])
            ->defaultSort('saved_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->relationship('game', 'name')
                    ->label('Game'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\TextEntry::make('user.name')->label('Player'),
            Infolists\Components\TextEntry::make('game.name'),
            Infolists\Components\TextEntry::make('save_key'),
            Infolists\Components\TextEntry::make('version'),
            Infolists\Components\TextEntry::make('checksum'),
            Infolists\Components\TextEntry::make('saved_at')->dateTime(),
            Infolists\Components\TextEntry::make('data')
                ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT))
                ->columnSpanFull(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGameSaves::route('/'),
            'view' => Pages\ViewGameSave::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
