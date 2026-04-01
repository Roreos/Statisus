<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\InvitationResource\Pages;
use App\Models\Invitation;
use Filament\Actions\Action as FilamentAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvitationResource extends Resource
{
    protected static ?string $model = Invitation::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('role')
                    ->options(['admin' => 'Admin', 'viewer' => 'Viewer'])
                    ->default('viewer')
                    ->required(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->searchable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->colors(['danger' => 'admin', 'info' => 'viewer']),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn (Invitation $r) => match (true) {
                        $r->isAccepted() => 'accepted',
                        $r->isExpired()  => 'expired',
                        default          => 'pending',
                    })
                    ->colors([
                        'success' => 'accepted',
                        'danger'  => 'expired',
                        'warning' => 'pending',
                    ]),

                Tables\Columns\TextColumn::make('inviter.name')->label('Invited by'),
                Tables\Columns\TextColumn::make('expires_at')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('accepted_at')->dateTime()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                FilamentAction::make('copy_link')
                    ->label('Copy link')
                    ->icon('heroicon-o-clipboard-document')
                    ->visible(fn (Invitation $r) => $r->isPending())
                    ->action(function (Invitation $record) {
                        Notification::make()
                            ->title('Invite link')
                            ->body($record->acceptUrl())
                            ->success()
                            ->send();
                    }),

                FilamentAction::make('regenerate')
                    ->label('Regenerate')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn (Invitation $r) => !$r->isAccepted())
                    ->action(function (Invitation $record) {
                        $record->update([
                            'token'      => Str::random(64),
                            'expires_at' => now()->addDays(7),
                        ]);
                        Notification::make()->title('Invitation regenerated')->success()->send();
                    }),

                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInvitations::route('/'),
            'create' => Pages\CreateInvitation::route('/create'),
        ];
    }
}
