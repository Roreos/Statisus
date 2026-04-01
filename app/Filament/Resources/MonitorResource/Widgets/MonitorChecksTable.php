<?php

namespace App\Filament\Resources\MonitorResource\Widgets;

use App\Models\MonitorCheck;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class MonitorChecksTable extends BaseWidget
{
    public ?int $monitorId = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Checks';

    public function mount(): void
    {
        // monitorId is passed via getFooterWidgetsColumns or record binding
        $this->monitorId = request()->route('record');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MonitorCheck::query()
                    ->where('monitor_id', $this->monitorId)
                    ->latest('checked_at')
            )
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'up',
                        'danger'  => 'down',
                        'warning' => 'degraded',
                    ]),

                Tables\Columns\TextColumn::make('response_time')
                    ->label('RTT')
                    ->formatStateUsing(fn ($state) => $state ? "{$state}ms" : '—')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 200    => 'success',
                        $state < 1000   => 'warning',
                        default         => 'danger',
                    }),

                Tables\Columns\TextColumn::make('status_code')
                    ->label('HTTP Code')
                    ->formatStateUsing(fn ($state) => $state ?? '—'),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('checked_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('checked_at', 'desc')
            ->paginated([25, 50, 100])
            ->poll('10s');
    }
}
