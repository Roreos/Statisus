<?php

namespace App\Services\Alerting;

use App\Models\Monitor;

class AlertPayload
{
    public function __construct(
        public readonly Monitor $monitor,
        public readonly string  $event,       // down, degraded, recovery
        public readonly string  $previousStatus,
        public readonly string  $newStatus,
        public readonly ?string $cause,
        public readonly int     $escalationLevel = 0,
    ) {}

    public function subject(): string
    {
        return match ($this->event) {
            'down'     => "🔴 DOWN: {$this->monitor->name}",
            'degraded' => "🟡 DEGRADED: {$this->monitor->name}",
            'recovery' => "🟢 RECOVERED: {$this->monitor->name}",
            default    => "Alert: {$this->monitor->name}",
        };
    }

    public function body(): string
    {
        $lines = [
            "Monitor: {$this->monitor->name}",
            "Target:  {$this->monitor->target}",
            "Status:  " . strtoupper($this->newStatus),
            "Time:    " . now()->toDateTimeString(),
        ];

        if ($this->cause) {
            $lines[] = "Reason:  {$this->cause}";
        }

        if ($this->escalationLevel > 0) {
            $lines[] = "⚠️ Escalation level {$this->escalationLevel}";
        }

        return implode("\n", $lines);
    }

    public function emoji(): string
    {
        return match ($this->event) {
            'down'     => '🔴',
            'degraded' => '🟡',
            'recovery' => '🟢',
            default    => '⚪',
        };
    }
}
