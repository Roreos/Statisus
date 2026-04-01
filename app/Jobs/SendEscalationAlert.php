<?php

namespace App\Jobs;

use App\Models\AlertRule;
use App\Models\Monitor;
use App\Services\Alerting\AlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEscalationAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 30;

    public function __construct(
        public readonly AlertRule $rule,
        public readonly Monitor   $monitor,
        public readonly string    $event,
        public readonly string    $previousStatus,
        public readonly string    $newStatus,
        public readonly ?string   $cause,
    ) {}

    public function handle(AlertService $service): void
    {
        $service->sendEscalation(
            $this->rule,
            $this->monitor,
            $this->event,
            $this->previousStatus,
            $this->newStatus,
            $this->cause,
        );
    }
}
