<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('display_name')->maxLength(50),
            Forms\Components\TextInput::make('email')->email(),
            Forms\Components\Toggle::make('is_anonymous'),
            Forms\Components\Toggle::make('is_banned'),
            Forms\Components\DateTimePicker::make('last_seen_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->default(fn ($record) => $record->name)
                    ->searchable(['display_name', 'name']),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\IconColumn::make('is_anonymous')->boolean(),
                Tables\Columns\IconColumn::make('is_banned')->boolean(),
                Tables\Columns\TextColumn::make('last_seen_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_anonymous'),
                Tables\Filters\TernaryFilter::make('is_banned'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AchievementsRelationManager::class,
            RelationManagers\GameSavesRelationManager::class,
            RelationManagers\LeaderboardEntriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
