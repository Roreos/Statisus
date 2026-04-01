<?php

namespace App\Filament\Widgets;

use App\Models\Incident;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentIncidentsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent Incidents';

    public function table(Table $table): Table
    {
        return $table
            ->query(Incident::query()->with('monitor')->latest('started_at')->limit(20))
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['danger' => 'open', 'success' => 'resolved']),

                Tables\Columns\TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('cause')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->since(),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : 'Ongoing')
                    ->color(fn (Incident $r) => $r->status === 'open' ? 'danger' : 'gray'),
            ])
            ->paginated(false)
            ->poll('15s');
    }
}
