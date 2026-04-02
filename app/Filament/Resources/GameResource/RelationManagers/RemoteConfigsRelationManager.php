<?php

namespace App\Filament\Resources\GameResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RemoteConfigsRelationManager extends RelationManager
{
    protected static string $relationship = 'remoteConfigs';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')
                ->required()
                ->maxLength(100)
                ->helperText('Config key (e.g., "feature_dark_mode", "max_hints")'),
            Forms\Components\Textarea::make('value')
                ->required()
                ->helperText('The config value (will be cast based on value_type)'),
            Forms\Components\Select::make('value_type')
                ->options([
                    'string' => 'String',
                    'int' => 'Integer',
                    'bool' => 'Boolean',
                    'json' => 'JSON',
                ])
                ->required()
                ->default('string'),
            Forms\Components\Textarea::make('description')
                ->helperText('Internal note about what this config controls'),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('key')->searchable(),
                Tables\Columns\TextColumn::make('value')->limit(50),
                Tables\Columns\TextColumn::make('value_type')->badge(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}
