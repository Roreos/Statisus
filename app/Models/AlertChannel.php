<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AlertChannel extends Model
{
    protected $fillable = ['name', 'type', 'is_enabled', 'config'];

    protected $casts = [
        'config'     => 'array',
        'is_enabled' => 'boolean',
    ];

    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(AlertRule::class, 'alert_rule_channels')
            ->withPivot('escalation_level');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'email'   => 'Email',
            'discord' => 'Discord',
            'slack'   => 'Slack',
            'webhook' => 'Webhook',
            'sms'     => 'SMS (Twilio)',
            'push'    => 'Push (Pushover)',
            default   => ucfirst($this->type),
        };
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'email'   => 'heroicon-o-envelope',
            'discord' => 'heroicon-o-chat-bubble-left-right',
            'slack'   => 'heroicon-o-hashtag',
            'webhook' => 'heroicon-o-arrow-top-right-on-square',
            'sms'     => 'heroicon-o-device-phone-mobile',
            'push'    => 'heroicon-o-bell',
            default   => 'heroicon-o-megaphone',
        };
    }
}
