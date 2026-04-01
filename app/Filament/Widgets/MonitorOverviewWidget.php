<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use App\Models\Monitor;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonitorOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $total    = Monitor::count();
        $up       = Monitor::where('status', 'up')->count();
        $down     = Monitor::where('status', 'down')->count();
        $degraded = Monitor::where('status', 'degraded')->count();
        $open     = Incident::where('status', 'open')->count();

        return [
            Stat::make('Total Monitors', $total)
                ->icon('heroicon-o-signal')
                ->color('gray'),

            Stat::make('Online', $up)
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Offline', $down)
                ->icon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Degraded', $degraded)
                ->icon('heroicon-o-exclamation-circle')
                ->color('warning'),

            Stat::make('Open Incidents', $open)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($open > 0 ? 'danger' : 'success'),

            Stat::make('Pending', Monitor::where('status', 'pending')->count())
                ->icon('heroicon-o-clock')
                ->color('gray'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '10s';
    }
}
