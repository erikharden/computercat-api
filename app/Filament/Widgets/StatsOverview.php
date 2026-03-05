<?php

namespace App\Filament\Widgets;

use App\Models\LeaderboardEntry;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = Carbon::today();

        return [
            Stat::make('Total Users', User::count()),
            Stat::make('New Today', User::whereDate('created_at', $today)->count()),
            Stat::make('Active Today', User::whereDate('last_seen_at', $today)->count())
                ->description('Users seen today'),
            Stat::make('Submissions Today', LeaderboardEntry::whereDate('submitted_at', $today)->count()),
        ];
    }
}
