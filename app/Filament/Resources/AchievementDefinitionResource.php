<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementDefinitionResource\Pages;
use App\Models\AchievementDefinition;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AchievementDefinitionResource extends Resource
{
    protected static ?string $model = AchievementDefinition::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Game Data';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('game_id')
                ->relationship('game', 'name')
                ->required(),
            Forms\Components\TextInput::make('slug')->required()->maxLength(50),
            Forms\Components\TextInput::make('name')->required()->maxLength(100),
            Forms\Components\TextInput::make('description')->required()->maxLength(255),
            Forms\Components\TextInput::make('icon')->required()->maxLength(20),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            Forms\Components\Toggle::make('is_secret')->default(false),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('game.name')->sortable(),
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_secret')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('user_achievements_count')
                    ->counts('userAchievements')
                    ->label('Unlocks'),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\SelectFilter::make('game_id')
                    ->relationship('game', 'name')
                    ->label('Game'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievementDefinitions::route('/'),
            'create' => Pages\CreateAchievementDefinition::route('/create'),
            'edit' => Pages\EditAchievementDefinition::route('/{record}/edit'),
        ];
    }
}
