<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Http;

class SlackDriver
{
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $webhookUrl = $channel->config['webhook_url'] ?? null;

        if (! $webhookUrl) {
            throw new \RuntimeException('Slack channel missing "webhook_url"');
        }

        $color = match ($payload->event) {
            'down'     => 'danger',
            'degraded' => 'warning',
            'recovery' => 'good',
            default    => '#cccccc',
        };

        $response = Http::post($webhookUrl, [
            'attachments' => [[
                'fallback'  => $payload->subject(),
                'color'     => $color,
                'title'     => $payload->subject(),
                'text'      => "```{$payload->body()}```",
                'mrkdwn_in' => ['text'],
                'ts'        => now()->timestamp,
            ]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Slack webhook failed: {$response->status()} {$response->body()}");
        }
    }
}
