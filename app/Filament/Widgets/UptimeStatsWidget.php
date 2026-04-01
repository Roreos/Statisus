<?php

namespace App\Filament\Widgets;

use App\Models\Monitor;
use App\Models\MonitorCheck;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UptimeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $avgUptime24h = Monitor::all()->avg(fn ($m) => $m->uptimePercentage(24));
        $avgUptime7d  = Monitor::all()->avg(fn ($m) => $m->uptimePercentage(168));
        $avgRtt       = MonitorCheck::where('checked_at', '>=', now()->subHour())
            ->whereNotNull('response_time')
            ->avg('response_time');

        $checksLastHour = MonitorCheck::where('checked_at', '>=', now()->subHour())->count();

        return [
            Stat::make('Avg Uptime (24h)', round($avgUptime24h ?? 100, 2) . '%')
                ->icon('heroicon-o-arrow-trending-up')
                ->color(($avgUptime24h ?? 100) >= 99 ? 'success' : (($avgUptime24h ?? 100) >= 95 ? 'warning' : 'danger')),

            Stat::make('Avg Uptime (7d)', round($avgUptime7d ?? 100, 2) . '%')
                ->icon('heroicon-o-calendar')
                ->color(($avgUptime7d ?? 100) >= 99 ? 'success' : (($avgUptime7d ?? 100) >= 95 ? 'warning' : 'danger')),

            Stat::make('Avg Response Time (1h)', $avgRtt ? round($avgRtt) . 'ms' : '—')
                ->icon('heroicon-o-clock')
                ->color($avgRtt === null ? 'gray' : ($avgRtt < 200 ? 'success' : ($avgRtt < 1000 ? 'warning' : 'danger'))),

            Stat::make('Checks (last hour)', $checksLastHour)
                ->icon('heroicon-o-bolt')
                ->color('info'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s';
    }
}
