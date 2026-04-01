<?php

namespace App\Filament\Resources\MonitorResource\Pages;

use App\Filament\Resources\MonitorResource;
use App\Jobs\RunMonitorCheck;
use App\Models\Monitor;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewMonitor extends ViewRecord
{
    protected static string $resource = MonitorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('check_now')
                ->label('Check Now')
                ->icon('heroicon-o-play')
                ->color('info')
                ->action(function () {
                    RunMonitorCheck::dispatch($this->record);
                    Notification::make()->title('Check dispatched')->success()->send();
                }),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Monitor Details')
                ->columns(3)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (Monitor $r) => $r->statusColor()),

                    TextEntry::make('type')
                        ->formatStateUsing(fn (Monitor $r) => $r->typeLabel()),

                    TextEntry::make('target'),

                    TextEntry::make('interval')
                        ->formatStateUsing(fn ($state) => $state < 60 ? "{$state}s" : ($state / 60) . 'm'),

                    TextEntry::make('last_checked_at')
                        ->since(),

                    TextEntry::make('last_status_change_at')
                        ->label('Status Changed')
                        ->since(),
                ]),

            Section::make('24h Stats')
                ->columns(3)
                ->schema([
                    TextEntry::make('uptime_24h')
                        ->label('Uptime')
                        ->getStateUsing(fn (Monitor $r) => $r->uptimePercentage(24) . '%'),

                    TextEntry::make('avg_response_time')
                        ->label('Avg Response Time')
                        ->getStateUsing(fn (Monitor $r) => $r->avgResponseTime(24) ? round($r->avgResponseTime(24)) . 'ms' : '—'),

                    TextEntry::make('consecutive_failures')
                        ->label('Consecutive Failures'),
                ]),
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            MonitorResource\Widgets\MonitorChecksTable::class,
            MonitorResource\Widgets\ResponseTimeChart::class,
        ];
    }
}
