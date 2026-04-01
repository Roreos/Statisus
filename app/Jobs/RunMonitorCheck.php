<?php

namespace App\Jobs;

use App\Models\Monitor;
use App\Services\MonitorDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunMonitorCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 30;
    public int $tries   = 1;

    public function __construct(public readonly Monitor $monitor) {}

    public function handle(MonitorDispatcher $dispatcher): void
    {
        if (! $this->monitor->is_enabled) {
            return;
        }

        $dispatcher->run($this->monitor);
    }
}
