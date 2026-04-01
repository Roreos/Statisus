<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 2;

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
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
                TextInput::make('job_title')->nullable(),
                Select::make('timezone')
                    ->options(collect(timezone_identifiers_list())->mapWithKeys(fn ($tz) => [$tz => $tz]))
                    ->searchable()
                    ->default('UTC'),
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->options(['admin' => 'Admin', 'viewer' => 'Viewer'])
                    ->required()
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn (User $r) => 'https://ui-avatars.com/api/?name=' . urlencode($r->name) . '&background=0891b2&color=fff'),

                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable(),

                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Role')
                    ->colors(['danger' => 'admin', 'info' => 'viewer']),

                Tables\Columns\TextColumn::make('job_title')->placeholder('—'),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable()->label('Joined'),
            ])
            ->actions([
                EditAction::make()
                    ->visible(fn (User $r) => !$r->is_root || Auth::user()?->is_root),
                DeleteAction::make()
                    ->visible(fn (User $r) => !$r->is_root && $r->id !== Auth::id()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
