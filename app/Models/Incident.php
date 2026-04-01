<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = [
        'monitor_id', 'status', 'cause',
        'started_at', 'resolved_at', 'duration_seconds',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    public function updates(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)->orderByDesc('posted_at');
    }

    public function latestUpdate(): HasMany
    {
        return $this->hasMany(IncidentUpdate::class)->latest('posted_at')->limit(1);
    }

    public function durationForHumans(): string
    {
        if (! $this->resolved_at) {
            return $this->started_at->diffForHumans(null, true);
        }

        return $this->started_at->diffForHumans($this->resolved_at, true);
    }
}
