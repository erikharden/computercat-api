<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Prune stale anonymous accounts weekly (Sunday 3 AM)
Schedule::command('users:prune-anonymous --days=90')->weeklyOn(0, '03:00');
