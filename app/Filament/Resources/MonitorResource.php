<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MonitorResource\Pages;
use App\Jobs\RunMonitorCheck;
use App\Models\Monitor;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class MonitorResource extends Resource
{
    protected static ?string $model = Monitor::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-signal';
    protected static ?int                    $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Monitoring;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('General')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Select::make('type')
                        ->required()
                        ->options([
                            'http'  => 'HTTP',
                            'https' => 'HTTPS',
                            'tcp'   => 'TCP / Port',
                            'ping'  => 'Ping (ICMP)',
                            'dns'   => 'DNS',
                            'ssl'   => 'SSL Certificate',
                        ])
                        ->live(),

                    TextInput::make('target')
                        ->required()
                        ->columnSpanFull(),

                    Select::make('interval')
                        ->label('Check Interval')
                        ->required()
                        ->options([
                            5    => 'Every 5 seconds',
                            10   => 'Every 10 seconds',
                            30   => 'Every 30 seconds',
                            60   => 'Every minute',
                            120  => 'Every 2 minutes',
                            300  => 'Every 5 minutes',
                            600  => 'Every 10 minutes',
                            1800 => 'Every 30 minutes',
                            3600 => 'Every hour',
                        ])
                        ->default(60),

                    TextInput::make('timeout')
                        ->label('Timeout (seconds)')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->maxValue(60),

                    TextInput::make('failure_threshold')
                        ->label('Failures before DOWN')
                        ->numeric()
                        ->default(1)
                        ->minValue(1),

                    Toggle::make('is_enabled')
                        ->label('Enabled')
                        ->default(true),
                ]),

            Section::make('HTTP Options')
                ->columns(2)
                ->visible(fn ($get) => in_array($get('type'), ['http', 'https']))
                ->schema([
                    Select::make('method')
                        ->options(['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'HEAD' => 'HEAD'])
                        ->default('GET'),

                    TextInput::make('expected_status_code')
                        ->label('Expected Status Code')
                        ->numeric()
                        ->placeholder('200'),

                    TextInput::make('expected_body_contains')
                        ->label('Response Body Contains')
                        ->placeholder('OK')
                        ->columnSpanFull(),

                    KeyValue::make('headers')
                        ->label('Request Headers')
                        ->columnSpanFull(),

                    Textarea::make('request_body')
                        ->label('Request Body')
                        ->rows(3)
                        ->columnSpanFull(),

                    Toggle::make('follow_redirects')
                        ->label('Follow Redirects')
                        ->default(true),

                    Toggle::make('verify_ssl')
                        ->label('Verify SSL Certificate')
                        ->default(true),
                ]),

            Section::make('TCP Options')
                ->visible(fn ($get) => $get('type') === 'tcp')
                ->schema([
                    TextInput::make('port')
                        ->label('Port')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(65535),
                ]),

            Section::make('DNS Options')
                ->columns(2)
                ->visible(fn ($get) => $get('type') === 'dns')
                ->schema([
                    Select::make('dns_record_type')
                        ->label('Record Type')
                        ->options(['A' => 'A', 'AAAA' => 'AAAA', 'CNAME' => 'CNAME', 'MX' => 'MX', 'TXT' => 'TXT', 'NS' => 'NS'])
                        ->default('A'),

                    TextInput::make('dns_expected_value')
                        ->label('Expected Value')
                        ->placeholder('1.2.3.4'),

                    TextInput::make('dns_nameserver')
                        ->label('Custom Nameserver')
                        ->placeholder('8.8.8.8'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('status')
                    ->icon(fn (Monitor $r) => $r->statusIcon())
                    ->color(fn (Monitor $r) => $r->statusColor())
                    ->tooltip(fn (Monitor $r) => ucfirst($r->status))
                    ->label(''),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (Monitor $r) => $r->typeLabel())
                    ->colors([
                        'info'    => fn ($s) => in_array($s, ['http', 'https']),
                        'warning' => 'tcp',
                        'success' => 'ping',
                        'gray'    => 'dns',
                    ]),

                Tables\Columns\TextColumn::make('target')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(fn (Monitor $r) => $r->target),

                Tables\Columns\TextColumn::make('interval')
                    ->formatStateUsing(fn ($state) => self::formatInterval($state))
                    ->label('Interval'),

                Tables\Columns\TextColumn::make('response_time')
                    ->label('Last RTT')
                    ->getStateUsing(fn (Monitor $r) => $r->checks()->latest('checked_at')->value('response_time'))
                    ->formatStateUsing(fn ($state) => $state ? "{$state}ms" : '—')
                    ->color(fn ($state) => match (true) {
                        $state === null => 'gray',
                        $state < 200    => 'success',
                        $state < 1000   => 'warning',
                        default         => 'danger',
                    }),

                Tables\Columns\TextColumn::make('uptime_24h')
                    ->label('Uptime 24h')
                    ->getStateUsing(fn (Monitor $r) => $r->uptimePercentage(24))
                    ->formatStateUsing(fn ($state) => "{$state}%")
                    ->color(fn ($state) => match (true) {
                        $state >= 99 => 'success',
                        $state >= 95 => 'warning',
                        default      => 'danger',
                    }),

                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Check')
                    ->since()
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_enabled')
                    ->label('Enabled'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['up' => 'Up', 'down' => 'Down', 'degraded' => 'Degraded', 'pending' => 'Pending']),

                Tables\Filters\SelectFilter::make('type')
                    ->options(['http' => 'HTTP', 'https' => 'HTTPS', 'tcp' => 'TCP', 'ping' => 'Ping', 'dns' => 'DNS']),

                Tables\Filters\TernaryFilter::make('is_enabled')->label('Enabled'),
            ])
            ->actions([
                FilamentAction::make('check_now')
                    ->label('Check Now')
                    ->icon('heroicon-o-play')
                    ->color('info')
                    ->action(function (Monitor $record) {
                        RunMonitorCheck::dispatch($record);
                        Notification::make()->title('Check dispatched')->success()->send();
                    }),

                FilamentAction::make('view_checks')
                    ->label('History')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Monitor $r) => MonitorResource\Pages\ViewMonitor::getUrl(['record' => $r])),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('enable')
                        ->label('Enable Selected')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => true]))
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('disable')
                        ->label('Disable Selected')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->update(['is_enabled' => false]))
                        ->deselectRecordsAfterCompletion(),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->poll('10s');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMonitors::route('/'),
            'create' => Pages\CreateMonitor::route('/create'),
            'edit'   => Pages\EditMonitor::route('/{record}/edit'),
            'view'   => Pages\ViewMonitor::route('/{record}'),
        ];
    }

    private static function formatInterval(int $seconds): string
    {
        if ($seconds < 60) return "{$seconds}s";
        if ($seconds < 3600) return ($seconds / 60) . 'm';
        return ($seconds / 3600) . 'h';
    }
}
