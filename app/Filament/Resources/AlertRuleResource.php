<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AlertRuleResource\Pages;
use App\Models\AlertChannel;
use App\Models\AlertRule;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AlertRuleResource extends Resource
{
    protected static ?string $model = AlertRule::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?int                    $navigationSort = 11;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Rule')->columns(2)->schema([
                Select::make('monitor_id')
                    ->label('Monitor')
                    ->relationship('monitor', 'name')
                    ->searchable()
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('name')->required()->columnSpanFull(),

                Toggle::make('is_enabled')->default(true)->columnSpanFull(),
            ]),

            Section::make('Trigger Conditions')->columns(3)->schema([
                Toggle::make('alert_on_down')
                    ->label('Alert on DOWN')
                    ->default(true),

                Toggle::make('alert_on_degraded')
                    ->label('Alert on DEGRADED')
                    ->default(false),

                Toggle::make('alert_on_recovery')
                    ->label('Alert on RECOVERY')
                    ->default(true),

                TextInput::make('failure_threshold')
                    ->label('Failures before alerting')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->helperText('Consecutive failures needed before sending the first alert'),
            ]),

            Section::make('Primary Channels')
                ->description('Notified immediately when the rule triggers')
                ->schema([
                    Select::make('primary_channel_ids')
                        ->label('Channels')
                        ->options(AlertChannel::where('is_enabled', true)->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->dehydrated(false),
                ]),

            Section::make('Escalation')
                ->description('If the monitor is still down after the delay, notify escalation channels')
                ->columns(2)
                ->schema([
                    Toggle::make('escalation_enabled')
                        ->label('Enable Escalation')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('escalation_after_minutes')
                        ->label('Escalate after (minutes)')
                        ->numeric()
                        ->default(30)
                        ->minValue(1)
                        ->visible(fn ($get) => $get('escalation_enabled')),

                    Select::make('escalation_channel_ids')
                        ->label('Escalation Channels')
                        ->options(AlertChannel::where('is_enabled', true)->pluck('name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->visible(fn ($get) => $get('escalation_enabled'))
                        ->dehydrated(false)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('monitor.name')
                    ->label('Monitor')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\IconColumn::make('alert_on_down')
                    ->label('Down')
                    ->boolean(),

                Tables\Columns\IconColumn::make('alert_on_degraded')
                    ->label('Degraded')
                    ->boolean(),

                Tables\Columns\IconColumn::make('alert_on_recovery')
                    ->label('Recovery')
                    ->boolean(),

                Tables\Columns\TextColumn::make('failure_threshold')
                    ->label('Threshold'),

                Tables\Columns\IconColumn::make('escalation_enabled')
                    ->label('Escalation')
                    ->boolean(),

                Tables\Columns\ToggleColumn::make('is_enabled')->label('Enabled'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlertRules::route('/'),
            'create' => Pages\CreateAlertRule::route('/create'),
            'edit'   => Pages\EditAlertRule::route('/{record}/edit'),
        ];
    }
}
