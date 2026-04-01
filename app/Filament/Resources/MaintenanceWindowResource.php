<?php

namespace App\Filament\Resources;

use App\Enums\NavigationGroup;
use App\Filament\Resources\MaintenanceWindowResource\Pages;
use App\Models\MaintenanceWindow;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;

class MaintenanceWindowResource extends Resource
{
    protected static ?string $model = MaintenanceWindow::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return NavigationGroup::Settings;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status_page_id')
                ->relationship('statusPage', 'name')
                ->required()
                ->searchable(),

            TextInput::make('title')
                ->required()
                ->maxLength(150),

            Textarea::make('description')
                ->rows(3),

            DateTimePicker::make('scheduled_at')
                ->required()
                ->label('Starts at'),

            DateTimePicker::make('ends_at')
                ->required()
                ->label('Ends at')
                ->after('scheduled_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('statusPage.name')
                    ->label('Status Page')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')->searchable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime(),

                Tables\Columns\BadgeColumn::make('status')
                    ->getStateUsing(fn (MaintenanceWindow $r) => match (true) {
                        $r->isActive()   => 'active',
                        $r->isUpcoming() => 'upcoming',
                        default          => 'past',
                    })
                    ->colors([
                        'warning' => 'active',
                        'info'    => 'upcoming',
                        'gray'    => 'past',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_page')
                    ->relationship('statusPage', 'name'),
            ])
            ->defaultSort('scheduled_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMaintenanceWindows::route('/'),
            'create' => Pages\CreateMaintenanceWindow::route('/create'),
            'edit'   => Pages\EditMaintenanceWindow::route('/{record}/edit'),
        ];
    }
}
