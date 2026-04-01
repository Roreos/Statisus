<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Http;

class DiscordDriver
{
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $webhookUrl = $channel->config['webhook_url'] ?? null;

        if (! $webhookUrl) {
            throw new \RuntimeException('Discord channel missing "webhook_url"');
        }

        $color = match ($payload->event) {
            'down'     => 15158332, // red
            'degraded' => 16776960, // yellow
            'recovery' => 3066993,  // green
            default    => 9807270,
        };

        $response = Http::post($webhookUrl, [
            'embeds' => [[
                'title'       => $payload->subject(),
                'description' => "```\n{$payload->body()}\n```",
                'color'       => $color,
                'timestamp'   => now()->toIso8601String(),
            ]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Discord webhook failed: {$response->status()} {$response->body()}");
        }
    }
}
