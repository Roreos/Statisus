<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Http;

class PushDriver
{
    // Uses Pushover API — simple, reliable push notifications
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $token   = $channel->config['app_token']  ?? config('services.pushover.token');
        $userKey = $channel->config['user_key']   ?? null;

        if (! $token || ! $userKey) {
            throw new \RuntimeException('Push channel missing Pushover credentials (app_token, user_key)');
        }

        $priority = match ($payload->event) {
            'down'     => 1,  // high priority
            'degraded' => 0,  // normal
            'recovery' => -1, // low
            default    => 0,
        };

        $response = Http::asForm()->post('https://api.pushover.net/1/messages.json', [
            'token'    => $token,
            'user'     => $userKey,
            'title'    => $payload->subject(),
            'message'  => $payload->body(),
            'priority' => $priority,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Pushover failed: {$response->status()} {$response->body()}");
        }
    }
}
