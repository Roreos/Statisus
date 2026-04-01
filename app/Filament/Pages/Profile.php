<?php

namespace App\Filament\Pages;

use App\Models\AlertLog;
use App\Models\Incident;
use App\Models\Monitor;
use App\Models\StatusPage;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use BackedEnum;

class Profile extends Page
{
    protected string $view = 'filament.pages.profile';
    protected static BackedEnum|string|null $navigationIcon  = 'heroicon-o-user-circle';
    protected static ?string                $navigationLabel = 'My Profile';
    protected static ?string                $title           = 'My Profile';
    protected static ?string                $slug            = 'me';
    protected static ?int                   $navigationSort  = 99;

    // Put it in no group — appears at the bottom of the nav
    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public function getViewData(): array
    {
        $user = Auth::user();

        $totalMonitors  = Monitor::count();
        $upMonitors     = Monitor::where('status', 'up')->count();
        $downMonitors   = Monitor::where('status', 'down')->count();
        $openIncidents  = Incident::where('status', 'open')->count();
        $totalIncidents = Incident::count();
        $alertsSent     = AlertLog::where('success', true)->count();
        $statusPages    = StatusPage::count();

        // Recent activity: last 15 alert logs with relations
        $recentAlerts = AlertLog::with(['monitor', 'channel'])
            ->latest('sent_at')
            ->limit(15)
            ->get();

        // Recent incidents
        $recentIncidents = Incident::with('monitor')
            ->latest('started_at')
            ->limit(8)
            ->get();

        return compact(
            'user',
            'totalMonitors', 'upMonitors', 'downMonitors',
            'openIncidents', 'totalIncidents',
            'alertsSent', 'statusPages',
            'recentAlerts', 'recentIncidents',
        );
    }
}
