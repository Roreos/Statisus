<?php

namespace App\Console\Commands;

use App\Jobs\RunMonitorCheck;
use App\Models\Monitor;
use Illuminate\Console\Command;

class DispatchMonitorChecks extends Command
{
    protected $signature   = 'monitors:dispatch';
    protected $description = 'Dispatch due monitor checks based on their intervals';

    public function handle(): void
    {
        $now = now();

        Monitor::where('is_enabled', true)
            ->get()
            ->each(function (Monitor $monitor) use ($now) {
                // Check if it's time to run based on interval
                if (
                    $monitor->last_checked_at === null ||
                    $monitor->last_checked_at->addSeconds($monitor->interval)->lte($now)
                ) {
                    RunMonitorCheck::dispatch($monitor);
                }
            });

        $this->info('Monitor checks dispatched.');
    }
}
