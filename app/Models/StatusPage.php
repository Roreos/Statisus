<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusPage extends Model
{
    protected $fillable = [
        'name', 'slug', 'custom_domain', 'logo_path',
        'primary_color', 'background_color', 'description',
        'is_public', 'show_incidents', 'show_maintenance', 'show_uptime_graph',
        'category_id',
    ];

    protected $casts = [
        'is_public'          => 'boolean',
        'show_incidents'     => 'boolean',
        'show_maintenance'   => 'boolean',
        'show_uptime_graph'  => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(StatusPageCategory::class, 'category_id');
    }

    public function monitors(): BelongsToMany
    {
        return $this->belongsToMany(Monitor::class, 'status_page_monitors')
            ->withPivot(['display_name', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function maintenanceWindows(): HasMany
    {
        return $this->hasMany(MaintenanceWindow::class);
    }

    public function upcomingMaintenance(): HasMany
    {
        return $this->hasMany(MaintenanceWindow::class)
            ->where('ends_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    /** Overall status: worst status across all monitors */
    public function overallStatus(): string
    {
        $statuses = $this->monitors->pluck('status');

        if ($statuses->contains('down')) {
            return 'down';
        }

        if ($statuses->contains('degraded')) {
            return 'degraded';
        }

        if ($statuses->contains('pending')) {
            return 'pending';
        }

        return 'up';
    }

    public function publicUrl(): string
    {
        if ($this->custom_domain) {
            return 'https://' . $this->custom_domain;
        }

        return url('/status/' . $this->slug);
    }
}
