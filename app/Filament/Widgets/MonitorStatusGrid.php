<?php

namespace App\Filament\Widgets;

use App\Models\Monitor;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MonitorStatusGrid extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'All Monitors';

    public function table(Table $table): Table
    {
        return $table
            ->query(Monitor::query()->with(['checks' => fn ($q) => $q->latest('checked_at')->limit(1)]))
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (Monitor $r) => $r->statusIcon())
                    ->color(fn (Monitor $r) => $r->statusColor())
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->weight('bold')
                    ->url(fn (Monitor $r) => route('filament.admin.resources.monitors.view', $r)),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (Monitor $r) => $r->typeLabel()),

                Tables\Columns\TextColumn::make('target')->limit(35),

                Tables\Columns\TextColumn::make('last_rtt')
                    ->label('RTT')
                    ->getStateUsing(fn (Monitor $r) => $r->checks->first()?->response_time)
                    ->formatStateUsing(fn ($state) => $state ? "{$state}ms" : '—')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 200    => 'success',
                        $state < 1000   => 'warning',
                        default         => 'danger',
                    }),

                Tables\Columns\TextColumn::make('uptime')
                    ->label('Uptime 24h')
                    ->getStateUsing(fn (Monitor $r) => $r->uptimePercentage(24) . '%')
                    ->color(fn (Monitor $r) => match (true) {
                        $r->uptimePercentage(24) >= 99 => 'success',
                        $r->uptimePercentage(24) >= 95 => 'warning',
                        default                        => 'danger',
                    }),

                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Check')
                    ->since(),
            ])
            ->defaultSort('name')
            ->paginated(false)
            ->poll('10s');
    }
}
