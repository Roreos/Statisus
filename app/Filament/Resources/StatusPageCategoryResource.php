<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\StatusPageCategoryResource\Pages;
use App\Models\StatusPageCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Illuminate\Support\Str;

class StatusPageCategoryResource extends Resource
{
    protected static ?string $model = StatusPageCategory::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->columns(2)->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(80)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),

                Select::make('icon')
                    ->options([
                        'circle'   => 'Circle (default)',
                        'server'   => 'Server',
                        'cloud'    => 'Cloud',
                        'globe'    => 'Globe',
                        'cpu'      => 'CPU / Chip',
                        'database' => 'Database',
                        'shield'   => 'Shield',
                        'bolt'     => 'Bolt',
                        'chart'    => 'Chart / Analytics',
                        'wrench'   => 'Wrench / Tools',
                    ])
                    ->default('circle')
                    ->required(),

                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->label('Sort order'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->color('gray'),
                Tables\Columns\TextColumn::make('icon'),
                Tables\Columns\TextColumn::make('status_pages_count')
                    ->counts('statusPages')
                    ->label('Pages'),
                Tables\Columns\TextColumn::make('sort_order')->sortable()->label('Order'),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStatusPageCategories::route('/'),
            'create' => Pages\CreateStatusPageCategory::route('/create'),
            'edit'   => Pages\EditStatusPageCategory::route('/{record}/edit'),
        ];
    }
}
