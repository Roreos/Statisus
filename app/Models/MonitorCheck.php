<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonitorCheck extends Model
{
    use MassPrunable;

    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('checked_at', '<', now()->subDays(30));
    }

    protected $fillable = [
        'monitor_id', 'status', 'response_time', 'status_code',
        'response_body', 'error_message', 'metadata', 'checked_at',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'checked_at' => 'datetime',
    ];

    public function monitor(): BelongsTo
    {
        return $this->belongsTo(Monitor::class);
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'up'       => 'success',
            'down'     => 'danger',
            'degraded' => 'warning',
            default    => 'gray',
        };
    }
}
