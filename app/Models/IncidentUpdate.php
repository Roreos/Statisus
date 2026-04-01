<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentUpdate extends Model
{
    protected $fillable = ['incident_id', 'status', 'message', 'posted_at'];

    protected $casts = ['posted_at' => 'datetime'];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'investigating' => 'Investigating',
            'identified'    => 'Identified',
            'monitoring'    => 'Monitoring',
            'resolved'      => 'Resolved',
            default         => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'investigating' => 'danger',
            'identified'    => 'warning',
            'monitoring'    => 'info',
            'resolved'      => 'success',
            default         => 'gray',
        };
    }
}
