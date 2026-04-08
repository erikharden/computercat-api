<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GameResource\Pages;
use App\Filament\Resources\GameResource\RelationManagers;
use App\Models\Game;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GameResource extends Resource
{
    protected static ?string $model = Game::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?int $navigationSort = 1;

    /**
     * Anti-cheat presets — named configurations that Mattias can pick from a dropdown.
     * Each preset configures bounds and proof requirements.
     */
    public const ANTI_CHEAT_PRESETS = [
        'none' => [
            'label' => 'None — no validation',
            'description' => 'Scores are accepted as-is. Use for casual games without competitive leaderboards.',
            'config' => [],
        ],
        'time_ms' => [
            'label' => 'Time-based (milliseconds)',
            'description' => 'Score = solve time in ms. Lower is better. Validates minimum/maximum time and optionally requires proof-of-play with FNV-1a signature.',
            'config' => [
                'score_unit' => 'ms',
                'min_score' => 5000,
                'max_score' => 86400000,
                'proof_algorithm' => 'fnv1a',
                'proof_time_tolerance' => 1000,
                'min_moves' => 1,
            ],
        ],
        'time_seconds' => [
            'label' => 'Time-based (seconds)',
            'description' => 'Score = solve time in seconds. Lower is better.',
            'config' => [
                'score_unit' => 'seconds',
                'min_score' => 5,
                'max_score' => 86400,
                'proof_algorithm' => 'fnv1a',
                'proof_time_tolerance' => 2,
                'min_moves' => 1,
            ],
        ],
        'points' => [
            'label' => 'Points-based (higher is better)',
            'description' => 'Score = points. Higher is better. Validates against a maximum cap.',
            'config' => [
                'min_score' => 0,
                'max_score' => 1000000,
            ],
        ],
        'points_with_proof' => [
            'label' => 'Points-based with proof',
            'description' => 'Score = points. Higher is better. Requires proof-of-play signature.',
            'config' => [
                'min_score' => 0,
                'max_score' => 1000000,
                'require_proof' => true,
                'proof_algorithm' => 'fnv1a',
                'min_moves' => 1,
            ],
        ],
    ];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Basic Info')
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(50)
                        ->unique(ignoreRecord: true)
                        ->helperText('URL-friendly identifier (e.g., "tocco", "wordcraft")'),
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ]),

            Forms\Components\Section::make('Anti-Cheat')
                ->description('How leaderboard scores are validated. The Dev Kit manual explains the chosen type to the AI assistant.')
                ->schema([
                    Forms\Components\Select::make('settings.anti_cheat_preset')
                        ->label('Preset')
                        ->options(collect(self::ANTI_CHEAT_PRESETS)->mapWithKeys(fn ($p, $k) => [$k => $p['label']]))
                        ->default('none')
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $preset = self::ANTI_CHEAT_PRESETS[$state] ?? null;
                            if ($preset) {
                                foreach ($preset['config'] as $key => $value) {
                                    $set("settings.anti_cheat.{$key}", $value);
                                }
                            }
                        })
                        ->helperText(fn (Get $get) => self::ANTI_CHEAT_PRESETS[$get('settings.anti_cheat_preset') ?? 'none']['description'] ?? ''),
                    Forms\Components\TextInput::make('settings.anti_cheat.min_score')
                        ->label('Minimum score')
                        ->numeric()
                        ->default(0)
                        ->helperText('Scores below this are rejected'),
                    Forms\Components\TextInput::make('settings.anti_cheat.max_score')
                        ->label('Maximum score')
                        ->numeric()
                        ->default(86400)
                        ->helperText('Scores above this are rejected'),
                    Forms\Components\Toggle::make('settings.anti_cheat.require_proof')
                        ->label('Require proof-of-play')
                        ->default(false)
                        ->helperText('If enabled, submissions without a proof object are rejected'),
                    Forms\Components\TextInput::make('settings.anti_cheat.min_moves')
                        ->label('Minimum moves/actions')
                        ->numeric()
                        ->default(1)
                        ->helperText('Minimum action count required in proof'),
                ]),

            Forms\Components\Section::make('Products & Ownership')
                ->description('Define what each product ID unlocks. Used by the /ownership endpoint.')
                ->collapsed()
                ->schema([
                    Forms\Components\Repeater::make('settings.product_grants')
                        ->label('Product grants')
                        ->schema([
                            Forms\Components\TextInput::make('product_id')
                                ->required()
                                ->helperText('App Store / Play Store product ID'),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'pack' => 'Content pack',
                                    'theme_pack' => 'Theme pack',
                                    'supporter' => 'Supporter (unlocks all)',
                                ])
                                ->required()
                                ->live(),
                            Forms\Components\TextInput::make('id')
                                ->label('Pack ID')
                                ->helperText('The pack/theme-pack ID this product unlocks')
                                ->visible(fn (Get $get) => in_array($get('type'), ['pack', 'theme_pack'])),
                        ])
                        ->columns(3)
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => ($state['product_id'] ?? '') . ' → ' . ($state['type'] ?? ''))
                        ->collapsible(),

                    Forms\Components\KeyValue::make('settings.theme_packs')
                        ->label('Theme pack contents')
                        ->keyLabel('Theme pack ID')
                        ->valueLabel('Theme IDs (comma-separated)')
                        ->helperText('Map theme pack IDs to the theme IDs they contain. Values are comma-separated.')
                        ->default([]),
                ]),

            Forms\Components\Section::make('RevenueCat Integration')
                ->description('Configure RevenueCat for in-app purchases. Public key is used by the client; webhook secret is used by the server to verify incoming events.')
                ->collapsed()
                ->schema([
                    Forms\Components\TextInput::make('settings.revenuecat.ios_public_key')
                        ->label('iOS public API key')
                        ->helperText('Starts with "appl_" — safe to ship in client'),
                    Forms\Components\TextInput::make('settings.revenuecat.android_public_key')
                        ->label('Android public API key')
                        ->helperText('Starts with "goog_"'),
                    Forms\Components\TextInput::make('settings.revenuecat.webhook_secret')
                        ->label('Webhook signing secret')
                        ->password()
                        ->revealable()
                        ->helperText('Leave blank to keep existing secret. To set/change: paste the same value you put in RevenueCat → Webhooks → Authorization header.')
                        // Always show empty on load — never echo back the stored secret
                        ->formatStateUsing(fn () => null)
                        // If input is empty, preserve the existing stored value;
                        // otherwise encrypt the new plaintext secret
                        ->dehydrateStateUsing(function ($state, ?Game $record) {
                            if (filled($state)) {
                                return \Illuminate\Support\Facades\Crypt::encryptString($state);
                            }

                            return $record?->settings['revenuecat']['webhook_secret'] ?? null;
                        }),
                    Forms\Components\Placeholder::make('webhook_url')
                        ->label('Webhook URL (for RevenueCat dashboard)')
                        ->content(fn ($record) => $record
                            ? rtrim(config('app.url'), '/') . "/api/v1/webhooks/revenuecat/{$record->slug}"
                            : 'Save the game first to generate the URL'),
                ]),

            Forms\Components\Section::make('Advanced Settings')
                ->description('Raw settings JSON for anything not covered above.')
                ->collapsed()
                ->schema([
                    Forms\Components\KeyValue::make('settings.extra')
                        ->label('Custom settings')
                        ->default([]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')->searchable(),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('leaderboard_types_count')
                    ->counts('leaderboardTypes')
                    ->label('Leaderboards'),
                Tables\Columns\TextColumn::make('achievement_definitions_count')
                    ->counts('achievementDefinitions')
                    ->label('Achievements'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('devkit')
                    ->label('Dev Kit')
                    ->icon('heroicon-o-command-line')
                    ->url(fn (Game $record) => static::getUrl('devkit', ['record' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LeaderboardTypesRelationManager::class,
            RelationManagers\AchievementDefinitionsRelationManager::class,
            RelationManagers\DailyContentPoolsRelationManager::class,
            RelationManagers\RemoteConfigsRelationManager::class,
            RelationManagers\GameEventsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGames::route('/'),
            'create' => Pages\CreateGame::route('/create'),
            'edit' => Pages\EditGame::route('/{record}/edit'),
            'devkit' => Pages\GameDevKit::route('/{record}/devkit'),
        ];
    }
}
