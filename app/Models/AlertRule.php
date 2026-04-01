<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlertRule extends Model
{
    protected $fillable = [
        'monitor_id', 'name', 'is_enabled',
        'alert_on_down', 'alert_on_degraded', 'alert_on_recovery',
        'failure_threshold',
        'escalation_enabled', 'escalation_after_minutes',
    ];

    protected $casts = [
        'is_enabled'          => 'boolean',
        'alert_on_down'       => 'boolean',
        'alert_on_degraded'   => 'boolean',
        'alert_on_recovery'   => 'boolean',
        'escalation_enabled'  => 'boolean',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    public function channels(): BelongsToMany
    {
        return $this->belongsToMany(AlertChannel::class, 'alert_rule_channels')
            ->withPivot('escalation_level');
    }

    public function primaryChannels(): BelongsToMany
    {
        return $this->channels()->wherePivot('escalation_level', 0);
    }

    public function escalationChannels(): BelongsToMany
    {
        return $this->channels()->wherePivot('escalation_level', '>', 0)->orderByPivot('escalation_level');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AlertLog::class);
    }
}
