<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\MonitorOverviewWidget;
use App\Filament\Widgets\MonitorStatusGrid;
use App\Filament\Widgets\RecentIncidentsWidget;
use App\Filament\Widgets\UptimeStatsWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?string                 $title          = 'Status Dashboard';

    public function getWidgets(): array
    {
        return [
            MonitorOverviewWidget::class,
            UptimeStatsWidget::class,
            MonitorStatusGrid::class,
            RecentIncidentsWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 12;
    }
}
