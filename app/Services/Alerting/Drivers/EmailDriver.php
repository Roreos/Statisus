<?php

namespace App\Services\Alerting\Drivers;

use App\Models\AlertChannel;
use App\Services\Alerting\AlertPayload;
use Illuminate\Support\Facades\Mail;

class EmailDriver
{
    public function send(AlertChannel $channel, AlertPayload $payload): void
    {
        $to = $channel->config['to'] ?? null;

        if (! $to) {
            throw new \RuntimeException('Email channel missing "to" address');
        }

        $recipients = array_map('trim', explode(',', $to));

        Mail::raw($payload->body(), function ($message) use ($recipients, $payload) {
            $message->to($recipients)
                    ->subject($payload->subject());
        });
    }
}
