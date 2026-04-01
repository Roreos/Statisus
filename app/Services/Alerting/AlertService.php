<?php

namespace App\Services\Alerting;

use App\Models\AlertChannel;
use App\Models\AlertLog;
use App\Models\AlertRule;
use App\Models\Monitor;
use App\Services\Alerting\Drivers\DiscordDriver;
use App\Services\Alerting\Drivers\EmailDriver;
use App\Services\Alerting\Drivers\PushDriver;
use App\Services\Alerting\Drivers\SlackDriver;
use App\Services\Alerting\Drivers\SmsDriver;
use App\Services\Alerting\Drivers\WebhookDriver;
use Illuminate\Support\Facades\Log;

class AlertService
{
    public function handleStatusChange(Monitor $monitor, string $previousStatus, string $newStatus, ?string $cause): void
    {
        $event = match ($newStatus) {
            'down'     => 'down',
            'degraded' => 'degraded',
            'up'       => 'recovery',
            default    => null,
        };

        if (! $event) {
            return;
        }

        $rules = $monitor->alertRules()
            ->where('is_enabled', true)
            ->with(['primaryChannels', 'escalationChannels'])
            ->get();

        foreach ($rules as $rule) {
            // Check if this event type is enabled on the rule
            if (! $this->ruleMatchesEvent($rule, $event)) {
                continue;
            }

            // For down events, respect the rule's failure threshold
            if ($event === 'down' && $monitor->consecutive_failures < $rule->failure_threshold) {
                continue;
            }

            $payload = new AlertPayload($monitor, $event, $previousStatus, $newStatus, $cause, 0);

            // Fire primary channels
            foreach ($rule->primaryChannels as $channel) {
                $this->dispatch($rule, $channel, $payload);
            }

            // Schedule escalation if enabled and event is down/degraded
            if ($rule->escalation_enabled && in_array($event, ['down', 'degraded'])) {
                \App\Jobs\SendEscalationAlert::dispatch($rule, $monitor, $event, $previousStatus, $newStatus, $cause)
                    ->delay(now()->addMinutes($rule->escalation_after_minutes));
            }
        }
    }

    public function sendEscalation(AlertRule $rule, Monitor $monitor, string $event, string $previousStatus, string $newStatus, ?string $cause): void
    {
        // Only escalate if monitor is still in the bad state
        $monitor->refresh();
        if ($monitor->status === 'up') {
            return;
        }

        $payload = new AlertPayload($monitor, $event, $previousStatus, $newStatus, $cause, 1);

        foreach ($rule->escalationChannels as $channel) {
            $this->dispatch($rule, $channel, $payload);
        }
    }

    public function dispatch(AlertRule $rule, AlertChannel $channel, AlertPayload $payload): void
    {
        if (! $channel->is_enabled) {
            return;
        }

        $success = true;
        $error   = null;

        try {
            $this->sendToDriver($channel, $payload);
        } catch (\Throwable $e) {
            $success = false;
            $error   = $e->getMessage();
            Log::error("Alert dispatch failed [{$channel->type}:{$channel->name}]: {$error}");
        }

        AlertLog::create([
            'monitor_id'       => $payload->monitor->id,
            'alert_rule_id'    => $rule->id,
            'alert_channel_id' => $channel->id,
            'event'            => $payload->event,
            'escalation_level' => $payload->escalationLevel,
            'success'          => $success,
            'error'            => $error,
            'sent_at'          => now(),
        ]);
    }

    private function sendToDriver(AlertChannel $channel, AlertPayload $payload): void
    {
        match ($channel->type) {
            'email'   => (new EmailDriver())->send($channel, $payload),
            'discord' => (new DiscordDriver())->send($channel, $payload),
            'slack'   => (new SlackDriver())->send($channel, $payload),
            'webhook' => (new WebhookDriver())->send($channel, $payload),
            'sms'     => (new SmsDriver())->send($channel, $payload),
            'push'    => (new PushDriver())->send($channel, $payload),
            default   => throw new \RuntimeException("Unknown channel type: {$channel->type}"),
        };
    }

    private function ruleMatchesEvent(AlertRule $rule, string $event): bool
    {
        return match ($event) {
            'down'     => $rule->alert_on_down,
            'degraded' => $rule->alert_on_degraded,
            'recovery' => $rule->alert_on_recovery,
            default    => false,
        };
    }
}
