<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Http;

class WebhookDriver
{
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $url    = $channel->config['url'] ?? null;
        $method = strtoupper($channel->config['method'] ?? 'POST');
        $secret = $channel->config['secret'] ?? null;

        if (! $url) {
            throw new \RuntimeException('Webhook channel missing "url"');
        }

        $body = [
            'event'            => $payload->event,
            'monitor_name'     => $payload->monitor->name,
            'monitor_target'   => $payload->monitor->target,
            'monitor_type'     => $payload->monitor->type,
            'previous_status'  => $payload->previousStatus,
            'new_status'       => $payload->newStatus,
            'cause'            => $payload->cause,
            'escalation_level' => $payload->escalationLevel,
            'timestamp'        => now()->toIso8601String(),
        ];

        $request = Http::timeout(10)->acceptJson();

        if ($secret) {
            $signature = hash_hmac('sha256', json_encode($body), $secret);
            $request   = $request->withHeader('X-Signature', $signature);
        }

        $response = $method === 'GET'
            ? $request->get($url, $body)
            : $request->post($url, $body);

        if (! $response->successful()) {
            throw new \RuntimeException("Webhook failed: {$response->status()} {$response->body()}");
        }
    }
}
