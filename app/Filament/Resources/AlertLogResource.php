<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AlertLogResource\Pages;
use App\Models\AlertLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AlertLogResource extends Resource
{
    protected static ?string $model = AlertLog::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?int                    $navigationSort = 3;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::History;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('success')
                    ->boolean()
                    ->label(''),

                Tables\Columns\BadgeColumn::make('event')
                    ->colors([
                        'danger'  => 'down',
                        'warning' => 'degraded',
                        'success' => 'recovery',
                    ]),

                Tables\Columns\TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('channel.type')
                    ->label('Type')
                    ->formatStateUsing(fn ($state) => ucfirst($state ?? '—')),

                Tables\Columns\TextColumn::make('escalation_level')
                    ->label('Escalation')
                    ->formatStateUsing(fn ($state) => $state === 0 ? 'Primary' : "Level {$state}"),

                Tables\Columns\TextColumn::make('error')
                    ->label('Error')
                    ->limit(50)
                    ->tooltip(fn ($state) => $state)
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->options(['down' => 'Down', 'degraded' => 'Degraded', 'recovery' => 'Recovery']),

                Tables\Filters\TernaryFilter::make('success')->label('Success'),

                Tables\Filters\SelectFilter::make('monitor')
                    ->relationship('monitor', 'name'),
            ])
            ->defaultSort('sent_at', 'desc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAlertLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
