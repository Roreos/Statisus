<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceWindow extends Model
{
    protected $fillable = [
        'status_page_id', 'title', 'description', 'scheduled_at', 'ends_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'ends_at'      => 'datetime',
    ];

    public function statusPage(): BelongsTo
    {
        return $this->belongsTo(StatusPage::class);
    }

    public function isActive(): bool
    {
        return now()->between($this->scheduled_at, $this->ends_at);
    }

    public function isUpcoming(): bool
    {
        return now()->lt($this->scheduled_at);
    }
}
