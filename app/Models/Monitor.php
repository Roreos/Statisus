<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monitor extends Model
{
    protected $fillable = [
        'name', 'type', 'target', 'port', 'interval', 'timeout',
        'is_enabled', 'method', 'headers', 'request_body',
        'expected_status_code', 'expected_body_contains',
        'follow_redirects', 'verify_ssl',
        'dns_record_type', 'dns_expected_value', 'dns_nameserver',
        'status', 'last_checked_at', 'last_status_change_at',
        'consecutive_failures', 'failure_threshold',
    ];

    protected $casts = [
        'headers'              => 'array',
        'is_enabled'           => 'boolean',
        'follow_redirects'     => 'boolean',
        'verify_ssl'           => 'boolean',
        'last_checked_at'      => 'datetime',
        'last_status_change_at'=> 'datetime',
    ];

    public function checks(): HasMany
    {
        return $this->hasMany(MonitorCheck::class);
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    public function alertRules(): HasMany
    {
        return $this->hasMany(AlertRule::class);
    }

    public function latestCheck(): HasMany
    {
        return $this->hasMany(MonitorCheck::class)->latest('checked_at')->limit(1);
    }

    public function recentChecks(int $limit = 60): \Illuminate\Database\Eloquent\Collection
    {
        return $this->checks()->latest('checked_at')->limit($limit)->get();
    }

    public function uptimePercentage(int $hours = 24): float
    {
        $total = $this->checks()
            ->where('checked_at', '>=', now()->subHours($hours))
            ->count();

        if ($total === 0) {
            return 100.0;
        }

        $up = $this->checks()
            ->where('checked_at', '>=', now()->subHours($hours))
            ->whereIn('status', ['up', 'degraded'])
            ->count();

        return round(($up / $total) * 100, 2);
    }

    public function avgResponseTime(int $hours = 24): ?float
    {
        return $this->checks()
            ->where('checked_at', '>=', now()->subHours($hours))
            ->whereNotNull('response_time')
            ->avg('response_time');
    }

    public function openIncident(): ?Incident
    {
        return $this->incidents()->where('status', 'open')->latest('started_at')->first();
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

    public function statusIcon(): string
    {
        return match ($this->status) {
            'up'       => 'heroicon-o-check-circle',
            'down'     => 'heroicon-o-x-circle',
            'degraded' => 'heroicon-o-exclamation-circle',
            default    => 'heroicon-o-clock',
        };
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'http', 'https' => strtoupper($this->type),
            'tcp'           => 'TCP/Port',
            'ping'          => 'Ping (ICMP)',
            'dns'           => 'DNS',
            'ssl'           => 'SSL Cert',
            default         => strtoupper($this->type),
        };
    }
}
