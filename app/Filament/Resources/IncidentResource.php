<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\IncidentResource\RelationManagers\UpdatesRelationManager;
use App\Filament\Resources\IncidentResource\Pages\ViewIncident;
use App\Filament\Resources\IncidentResource\Pages\ListIncidents;
use App\Models\Incident;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?int                    $navigationSort = 2;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::History;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['danger' => 'open', 'success' => 'resolved']),

                Tables\Columns\TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('cause')
                    ->limit(60)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('updates_count')
                    ->counts('updates')
                    ->label('Updates')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),

                Tables\Columns\TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('resolved_at')
                    ->label('Resolved')
                    ->dateTime()
                    ->placeholder('Ongoing'),

                Tables\Columns\TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state ? gmdate('H:i:s', $state) : '—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['open' => 'Open', 'resolved' => 'Resolved']),

                Tables\Filters\SelectFilter::make('monitor')
                    ->relationship('monitor', 'name'),
            ])
            ->defaultSort('started_at', 'desc')
            ->poll('15s')
            ->recordUrl(fn (Incident $r) => ViewIncident::getUrl(['record' => $r]));
    }

    public static function getRelationManagers(): array
    {
        return [
            UpdatesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIncidents::route('/'),
            'view'  => ViewIncident::route('/{record}'),
        ];
    }
}
