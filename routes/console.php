<?php

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Monitor Checks
|--------------------------------------------------------------------------
| Dispatches a check job for every enabled monitor whose interval is due.
| The command itself filters by each monitor's configured interval.
*/
Schedule::command('monitors:dispatch')->everyMinute();

/*
|--------------------------------------------------------------------------
| Pruning
|--------------------------------------------------------------------------
*/
// Remove monitor checks older than 30 days
Schedule::command('model:prune', ['--model' => \App\Models\MonitorCheck::class])->daily();

// Remove accepted/expired invitations older than 30 days
Schedule::command('model:prune', ['--model' => \App\Models\Invitation::class])->weekly();

/*
|--------------------------------------------------------------------------
| Queue Health
|--------------------------------------------------------------------------
| Restart the queue worker daily to prevent memory leaks.
| Only useful when QUEUE_CONNECTION != sync.
*/
Schedule::command('queue:restart')->daily();
