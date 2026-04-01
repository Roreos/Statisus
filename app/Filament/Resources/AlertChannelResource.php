<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\AlertChannelResource\Pages;
use App\Models\AlertChannel;
use App\Models\Monitor;
use App\Services\Alerting\AlertPayload;
use App\Services\Alerting\Drivers\DiscordDriver;
use App\Services\Alerting\Drivers\EmailDriver;
use App\Services\Alerting\Drivers\PushDriver;
use App\Services\Alerting\Drivers\SlackDriver;
use App\Services\Alerting\Drivers\SmsDriver;
use App\Services\Alerting\Drivers\WebhookDriver;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class AlertChannelResource extends Resource
{
    protected static ?string $model = AlertChannel::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';
    protected static ?int                    $navigationSort = 10;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')->required()->columnSpanFull(),

                Select::make('type')
                    ->required()
                    ->live()
                    ->options([
                        'email'   => '📧 Email',
                        'discord' => '💬 Discord',
                        'slack'   => '#️⃣ Slack',
                        'webhook' => '🔗 Webhook',
                        'sms'     => '📱 SMS (Twilio)',
                        'push'    => '🔔 Push (Pushover)',
                    ]),

                Toggle::make('is_enabled')->default(true)->columnSpanFull(),
            ]),

            Section::make('Email Config')
                ->visible(fn ($get) => $get('type') === 'email')
                ->schema([
                    TextInput::make('config.to')
                        ->label('To (comma-separated)')
                        ->placeholder('ops@example.com, cto@example.com')
                        ->dehydratedWhenHidden()
                        ->columnSpanFull(),
                ]),

            Section::make('Discord Config')
                ->visible(fn ($get) => $get('type') === 'discord')
                ->schema([
                    TextInput::make('config.webhook_url')
                        ->label('Webhook URL')
                        ->dehydratedWhenHidden()
                        ->columnSpanFull(),
                ]),

            Section::make('Slack Config')
                ->visible(fn ($get) => $get('type') === 'slack')
                ->schema([
                    TextInput::make('config.webhook_url')
                        ->label('Incoming Webhook URL')
                        ->dehydratedWhenHidden()
                        ->columnSpanFull(),
                ]),

            Section::make('Webhook Config')
                ->columns(2)
                ->visible(fn ($get) => $get('type') === 'webhook')
                ->schema([
                    TextInput::make('config.url')->label('URL')->dehydratedWhenHidden()->columnSpanFull(),
                    Select::make('config.method')
                        ->label('HTTP Method')
                        ->options(['POST' => 'POST', 'GET' => 'GET'])
                        ->default('POST')
                        ->dehydratedWhenHidden(),
                    TextInput::make('config.secret')
                        ->label('HMAC Secret (optional)')
                        ->password()
                        ->revealable()
                        ->dehydratedWhenHidden(),
                ]),

            Section::make('SMS Config (Twilio)')
                ->columns(2)
                ->visible(fn ($get) => $get('type') === 'sms')
                ->schema([
                    TextInput::make('config.account_sid')->label('Account SID')->dehydratedWhenHidden(),
                    TextInput::make('config.auth_token')->label('Auth Token')->password()->revealable()->dehydratedWhenHidden(),
                    TextInput::make('config.from')->label('From Number')->placeholder('+15551234567')->dehydratedWhenHidden(),
                    TextInput::make('config.to')->label('To Number')->placeholder('+15559876543')->dehydratedWhenHidden(),
                ]),

            Section::make('Push Config (Pushover)')
                ->columns(2)
                ->visible(fn ($get) => $get('type') === 'push')
                ->schema([
                    TextInput::make('config.app_token')->label('App Token')->password()->revealable()->dehydratedWhenHidden(),
                    TextInput::make('config.user_key')->label('User Key')->dehydratedWhenHidden(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('type')
                    ->icon(fn (AlertChannel $r) => $r->typeIcon())
                    ->label(''),

                Tables\Columns\TextColumn::make('name')->searchable()->weight('bold'),

                Tables\Columns\BadgeColumn::make('type')
                    ->formatStateUsing(fn (AlertChannel $r) => $r->typeLabel()),

                Tables\Columns\TextColumn::make('rules_count')
                    ->label('Used in Rules')
                    ->counts('rules'),

                Tables\Columns\ToggleColumn::make('is_enabled')->label('Enabled'),
            ])
            ->actions([
                FilamentAction::make('test')
                    ->label('Test')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->action(function (AlertChannel $record) {
                        $fakeMonitor         = new Monitor();
                        $fakeMonitor->name   = 'Test Monitor';
                        $fakeMonitor->target = 'https://example.com';
                        $fakeMonitor->type   = 'https';

                        $payload = new AlertPayload($fakeMonitor, 'down', 'up', 'down', 'This is a test alert', 0);

                        try {
                            match ($record->type) {
                                'email'   => (new EmailDriver())->send($record, $payload),
                                'discord' => (new DiscordDriver())->send($record, $payload),
                                'slack'   => (new SlackDriver())->send($record, $payload),
                                'webhook' => (new WebhookDriver())->send($record, $payload),
                                'sms'     => (new SmsDriver())->send($record, $payload),
                                'push'    => (new PushDriver())->send($record, $payload),
                            };
                            Notification::make()->title('Test alert sent successfully')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Test failed: ' . $e->getMessage())->danger()->send();
                        }
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAlertChannels::route('/'),
            'create' => Pages\CreateAlertChannel::route('/create'),
            'edit'   => Pages\EditAlertChannel::route('/{record}/edit'),
        ];
    }
}
