<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\StatusPageResource\Pages;
use App\Models\StatusPage;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Illuminate\Support\Str;

class StatusPageResource extends Resource
{
    protected static ?string $model = StatusPage::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page Identity')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn ($state, $set) =>
                            $set('slug', Str::slug($state))
                        ),

                    TextInput::make('slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->prefix(url('/status/'))
                        ->helperText('Public URL path'),

                    Textarea::make('description')
                        ->columnSpanFull()
                        ->rows(2),

                    TextInput::make('custom_domain')
                        ->columnSpanFull()
                        ->placeholder('status.yourcompany.com')
                        ->helperText('Optional. Point your DNS CNAME to this server.'),

                    \Filament\Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->nullable()
                        ->placeholder('No category')
                        ->columnSpanFull(),
                ]),

            Section::make('Branding')
                ->columns(2)
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->directory('status-page-logos')
                        ->columnSpanFull(),

                    ColorPicker::make('primary_color')
                        ->label('Primary Color')
                        ->default('#0ea5e9'),

                    ColorPicker::make('background_color')
                        ->label('Background Color')
                        ->default('#0f172a'),
                ]),

            Section::make('Monitors')
                ->schema([
                    CheckboxList::make('monitors')
                        ->relationship('monitors', 'name')
                        ->label('Monitors shown on this page')
                        ->searchable()
                        ->columns(2),
                ]),

            Section::make('Visibility')
                ->columns(2)
                ->schema([
                    Toggle::make('is_public')->default(true),
                    Toggle::make('show_incidents')->default(true),
                    Toggle::make('show_maintenance')->default(true),
                    Toggle::make('show_uptime_graph')->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->url(fn (StatusPage $r) => url('/status/' . $r->slug))
                    ->openUrlInNewTab()
                    ->color('info'),

                Tables\Columns\TextColumn::make('custom_domain')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('monitors_count')
                    ->counts('monitors')
                    ->label('Monitors'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (StatusPage $r) => url('/status/' . $r->slug))
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStatusPages::route('/'),
            'create' => Pages\CreateStatusPage::route('/create'),
            'edit'   => Pages\EditStatusPage::route('/{record}/edit'),
        ];
    }
}
