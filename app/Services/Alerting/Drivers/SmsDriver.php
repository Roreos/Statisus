<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Http;

class SmsDriver
{
    // Uses Twilio REST API directly — no SDK needed
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $sid   = $channel->config['account_sid']  ?? config('services.twilio.sid');
        $token = $channel->config['auth_token']   ?? config('services.twilio.token');
        $from  = $channel->config['from']         ?? config('services.twilio.from');
        $to    = $channel->config['to']            ?? null;

        if (! $sid || ! $token || ! $from || ! $to) {
            throw new \RuntimeException('SMS channel missing Twilio credentials (account_sid, auth_token, from, to)');
        }

        // Keep SMS short
        $message = "{$payload->emoji()} {$payload->monitor->name}: " . strtoupper($payload->newStatus);
        if ($payload->cause) {
            $message .= " — " . substr($payload->cause, 0, 100);
        }

        $response = Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'From' => $from,
                'To'   => $to,
                'Body' => $message,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException("Twilio SMS failed: {$response->status()} {$response->body()}");
        }
    }
}
