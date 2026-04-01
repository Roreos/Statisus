<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\Monitor;
use App\Models\MonitorCheck;
use App\Services\Alerting\AlertService;
use App\Services\Checkers\CheckResult;
use App\Services\Checkers\DnsChecker;
use App\Services\Checkers\HttpChecker;
use App\Services\Checkers\PingChecker;
use App\Services\Checkers\SslChecker;
use App\Services\Checkers\TcpChecker;

class MonitorDispatcher
{
    public function run(Monitor $monitor): MonitorCheck
    {
        $result = $this->runChecker($monitor);

        $check = MonitorCheck::create([
            'monitor_id'    => $monitor->id,
            'status'        => $result->status,
            'response_time' => $result->responseTime,
            'status_code'   => $result->statusCode,
            'response_body' => $result->responseBody,
            'error_message' => $result->errorMessage,
            'metadata'      => $result->metadata,
            'checked_at'    => now(),
        ]);

        $this->updateMonitorStatus($monitor, $result);

        return $check;
    }

    private function runChecker(Monitor $monitor): CheckResult
    {
        return match ($monitor->type) {
            'http', 'https' => (new HttpChecker())->check($monitor),
            'tcp'           => (new TcpChecker())->check($monitor),
            'ping'          => (new PingChecker())->check($monitor),
            'dns'           => (new DnsChecker())->check($monitor),
            'ssl'           => (new SslChecker())->check($monitor),
            default         => CheckResult::down("Unknown monitor type: {$monitor->type}"),
        };
    }

    private function updateMonitorStatus(Monitor $monitor, CheckResult $result): void
    {
        $previousStatus = $monitor->status;

        if ($result->status === 'down') {
            $monitor->consecutive_failures++;
        } else {
            $monitor->consecutive_failures = 0;
        }

        // Only mark as down after threshold is reached
        $effectiveStatus = ($result->status === 'down' && $monitor->consecutive_failures < $monitor->failure_threshold)
            ? $previousStatus
            : $result->status;

        $statusChanged = $previousStatus !== $effectiveStatus;

        $monitor->status         = $effectiveStatus;
        $monitor->last_checked_at = now();

        if ($statusChanged) {
            $monitor->last_status_change_at = now();
        }

        $monitor->save();

        // Incident management
        if ($statusChanged) {
            $this->handleIncident($monitor, $effectiveStatus, $result->errorMessage);
            app(AlertService::class)->handleStatusChange($monitor, $previousStatus, $effectiveStatus, $result->errorMessage);
        }
    }

    private function handleIncident(Monitor $monitor, string $newStatus, ?string $cause): void
    {
        $openIncident = $monitor->openIncident();

        if ($newStatus === 'down' || $newStatus === 'degraded') {
            if (! $openIncident) {
                Incident::create([
                    'monitor_id' => $monitor->id,
                    'status'     => 'open',
                    'cause'      => $cause,
                    'started_at' => now(),
                ]);
            }
        } elseif ($newStatus === 'up' && $openIncident) {
            $openIncident->update([
                'status'           => 'resolved',
                'resolved_at'      => now(),
                'duration_seconds' => now()->diffInSeconds($openIncident->started_at),
            ]);
        }
    }
}
