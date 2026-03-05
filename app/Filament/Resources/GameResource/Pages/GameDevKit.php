<?php

namespace App\Filament\Resources\GameResource\Pages;

use App\Filament\Resources\GameResource;
use App\Models\Game;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Forms\Form;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

class GameDevKit extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = GameResource::class;
    protected static string $view = 'filament.resources.game-resource.pages.game-dev-kit';
    protected static ?string $title = 'Developer Kit';

    public array $features = ['auth', 'leaderboards', 'achievements', 'saves'];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->load(['leaderboardTypes', 'achievementDefinitions']);

        $features = ['auth'];
        if ($this->record->leaderboardTypes->count()) $features[] = 'leaderboards';
        if ($this->record->achievementDefinitions->count()) $features[] = 'achievements';
        $features[] = 'saves';
        $this->features = $features;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            CheckboxList::make('features')
                ->label('Features to include')
                ->options([
                    'auth' => 'Authentication',
                    'leaderboards' => 'Leaderboards',
                    'achievements' => 'Achievements',
                    'saves' => 'Cloud Saves',
                    'purchases' => 'Purchases',
                ])
                ->live(),
        ]);
    }

    public function getSetupGuide(): string
    {
        $game = $this->record;
        $lines = [];

        $lines[] = "## 1. Game Configuration";
        $lines[] = "- **Name:** {$game->name}";
        $lines[] = "- **Slug:** `{$game->slug}`";
        $lines[] = "- **Status:** " . ($game->is_active ? 'Active' : 'Inactive');
        $lines[] = "";

        if ($game->leaderboardTypes->count()) {
            $lines[] = "## 2. Leaderboards ({$game->leaderboardTypes->count()})";
            foreach ($game->leaderboardTypes as $lb) {
                $lines[] = "- **{$lb->name}** (`{$lb->slug}`) — {$lb->period}, sort: {$lb->sort_direction}, label: \"{$lb->score_label}\"";
            }
            $lines[] = "";
        }

        if ($game->achievementDefinitions->count()) {
            $lines[] = "## 3. Achievements ({$game->achievementDefinitions->count()})";
            $lines[] = "Manage via the Edit page > Achievements tab.";
            $lines[] = "";
        }

        $lines[] = "## Next Step";
        $lines[] = "Copy the **AI Agent Manual** below and paste it into your AI coding assistant's context.";

        return implode("\n", $lines);
    }

    public function getManual(): string
    {
        $game = $this->record;
        $baseUrl = rtrim(config('app.url'), '/') . '/api/v1';
        $features = $this->features;
        $lines = [];

        $lines[] = "# Computer Cat API — Integration Manual";
        $lines[] = "";
        $lines[] = "You are integrating \"{$game->name}\" with the Computer Cat backend.";
        $lines[] = "Game slug: `{$game->slug}`";
        $lines[] = "";
        $lines[] = "## Base URL";
        $lines[] = "```";
        $lines[] = $baseUrl;
        $lines[] = "```";
        $lines[] = "";
        $lines[] = "All authenticated requests need: `Authorization: Bearer <token>`";
        $lines[] = "";

        $lines[] = "## Architecture";
        $lines[] = "";
        $lines[] = "Build **offline-first**:";
        $lines[] = "- Game logic is 100% client-side. API is for sync only.";
        $lines[] = "- Store everything in local storage. Push to cloud in background.";
        $lines[] = "- Game must work perfectly without internet.";
        $lines[] = "- On launch: pull cloud → merge with local → push merged state.";
        $lines[] = "";

        if (in_array('auth', $features)) {
            $lines[] = "## Authentication";
            $lines[] = "";
            $lines[] = "Use anonymous-first auth. Create a user silently on first launch.";
            $lines[] = "";
            $lines[] = "### Create anonymous user";
            $lines[] = "```";
            $lines[] = "POST {$baseUrl}/auth/anonymous";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"display_name": "Player"}';
            $lines[] = "```";
            $lines[] = "Response: `{\"token\": \"1|abc...\", \"user\": {\"id\": 1, \"display_name\": \"Player\", \"is_anonymous\": true}}`";
            $lines[] = "";
            $lines[] = "Store token persistently. Use for all subsequent requests.";
            $lines[] = "";
            $lines[] = "### Verify on launch";
            $lines[] = "```";
            $lines[] = "GET {$baseUrl}/auth/me";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "```";
            $lines[] = "If 401 → create new anonymous user.";
            $lines[] = "";
            $lines[] = "### Upgrade to email (optional)";
            $lines[] = "```";
            $lines[] = "POST {$baseUrl}/auth/register";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"name": "Name", "email": "a@b.com", "password": "secret", "password_confirmation": "secret"}';
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "### Update display name";
            $lines[] = "```";
            $lines[] = "PATCH {$baseUrl}/auth/me";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = '{"display_name": "NewName"}';
            $lines[] = "```";
            $lines[] = "";
        }

        if (in_array('leaderboards', $features) && $game->leaderboardTypes->count()) {
            $lines[] = "## Leaderboards";
            $lines[] = "";
            $lines[] = "### Configured types";
            foreach ($game->leaderboardTypes as $lb) {
                $dir = $lb->sort_direction === 'asc' ? 'lower is better' : 'higher is better';
                $lines[] = "- `{$lb->slug}` — {$lb->name}, {$lb->period}, {$dir}, label: \"{$lb->score_label}\"";
            }
            $lines[] = "";

            $ex = $game->leaderboardTypes->first();
            $lines[] = "### Submit score";
            $lines[] = "```";
            $lines[] = "POST {$baseUrl}/games/{$game->slug}/leaderboards/{$ex->slug}";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"score": 4250, "metadata": {"level": "6x6"}}';
            $lines[] = "```";
            $lines[] = "- `score`: integer (e.g. milliseconds for time)";
            $lines[] = "- `metadata`: optional context JSON";
            $lines[] = "- Best score per user per period is kept automatically";
            $lines[] = "";
            $lines[] = "### Get leaderboard";
            $lines[] = "```";
            $lines[] = "GET {$baseUrl}/games/{$game->slug}/leaderboards/{$ex->slug}";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "### Get my rank";
            $lines[] = "```";
            $lines[] = "GET {$baseUrl}/games/{$game->slug}/leaderboards/{$ex->slug}/me";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "Submit in background after completing a round. Queue if offline.";
            $lines[] = "";
        }

        if (in_array('achievements', $features) && $game->achievementDefinitions->count()) {
            $lines[] = "## Achievements";
            $lines[] = "";
            $lines[] = "Evaluated **client-side**. Server stores unlock state for cross-device sync.";
            $lines[] = "";
            $lines[] = "### Definitions";
            $lines[] = "| Slug | Name | Description | Icon |";
            $lines[] = "|------|------|-------------|------|";
            foreach ($game->achievementDefinitions->sortBy('sort_order') as $a) {
                $s = $a->is_secret ? ' (secret)' : '';
                $lines[] = "| `{$a->slug}` | {$a->name}{$s} | {$a->description} | {$a->icon} |";
            }
            $lines[] = "";
            $lines[] = "### Report unlocks";
            $lines[] = "```";
            $lines[] = "POST {$baseUrl}/games/{$game->slug}/achievements";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"slugs": ["first-win", "speed-demon"]}';
            $lines[] = "```";
            $lines[] = "Idempotent — already-unlocked slugs are ignored. Fire-and-forget.";
            $lines[] = "";
            $lines[] = "### Get my achievements";
            $lines[] = "```";
            $lines[] = "GET {$baseUrl}/games/{$game->slug}/achievements/me";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "### Pattern";
            $lines[] = "1. Define unlock conditions in client code";
            $lines[] = "2. Check conditions after each game action";
            $lines[] = "3. New unlocks → store locally AND POST to server";
            $lines[] = "4. On sync: union of server + local unlocks";
            $lines[] = "";
        }

        if (in_array('saves', $features)) {
            $lines[] = "## Cloud Saves";
            $lines[] = "";
            $lines[] = "Opaque JSON blob. Client owns the schema. Optimistic locking via `version`.";
            $lines[] = "";
            $lines[] = "### Save";
            $lines[] = "```";
            $lines[] = "PUT {$baseUrl}/games/{$game->slug}/saves/main";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"version": 1, "data": {"progress": {}, "settings": {}}}';
            $lines[] = "```";
            $lines[] = "- `version` must match server. Mismatch → 409 Conflict.";
            $lines[] = "- Returns new version on success.";
            $lines[] = "- `main` is default key. Use other keys for save slots.";
            $lines[] = "";
            $lines[] = "### Load";
            $lines[] = "```";
            $lines[] = "GET {$baseUrl}/games/{$game->slug}/saves/main";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "```";
            $lines[] = "";
            $lines[] = "### Sync strategy";
            $lines[] = "1. Launch → GET /saves/main";
            $lines[] = "2. Merge cloud + local (best scores, union achievements, latest settings)";
            $lines[] = "3. PUT merged state back";
            $lines[] = "4. During play: debounce pushes (3s after last change)";
            $lines[] = "5. On 409: pull, re-merge, retry";
            $lines[] = "";
        }

        if (in_array('purchases', $features)) {
            $lines[] = "## Purchases";
            $lines[] = "";
            $lines[] = "### Verify receipt";
            $lines[] = "```";
            $lines[] = "POST {$baseUrl}/purchases/verify";
            $lines[] = "Authorization: Bearer <token>";
            $lines[] = "Content-Type: application/json";
            $lines[] = "";
            $lines[] = '{"game_id": ' . $game->id . ', "product_id": "com.example.premium", "store": "apple", "transaction_id": "...", "receipt_data": "..."}';
            $lines[] = "```";
            $lines[] = "`store`: `apple`, `google`, or `web`. `transaction_id` is deduplicated.";
            $lines[] = "";
        }

        $lines[] = "## Error Handling";
        $lines[] = "";
        $lines[] = "All errors return JSON: `{\"message\": \"...\", \"errors\": {...}}`";
        $lines[] = "";
        $lines[] = "| Status | Meaning | Action |";
        $lines[] = "|--------|---------|--------|";
        $lines[] = "| 401 | Token expired | Create new anonymous user |";
        $lines[] = "| 404 | Not found | — |";
        $lines[] = "| 409 | Save conflict | Pull, merge, retry |";
        $lines[] = "| 422 | Validation | Check `errors` |";
        $lines[] = "| 429 | Rate limited | Back off, retry |";
        $lines[] = "";
        $lines[] = "## Rate Limits";
        $lines[] = "- General: 60/min";
        $lines[] = "- Auth: 10/min";
        $lines[] = "- Score submit: 30/min";

        return implode("\n", $lines);
    }
}
