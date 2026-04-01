<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertLog extends Model
{
    protected $fillable = [
        'monitor_id', 'alert_rule_id', 'alert_channel_id',
        'event', 'escalation_level', 'success', 'error', 'sent_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function monitor(): BelongsTo   { return $this->belongsTo(Monitor::class); }
    public function rule(): BelongsTo      { return $this->belongsTo(AlertRule::class, 'alert_rule_id'); }
    public function channel(): BelongsTo   { return $this->belongsTo(AlertChannel::class, 'alert_channel_id'); }
}
