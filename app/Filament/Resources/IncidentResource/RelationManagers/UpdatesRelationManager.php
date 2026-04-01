<?php

namespace App\Filament\Resources\IncidentResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class UpdatesRelationManager extends RelationManager
{
    protected static string $relationship = 'updates';
    protected static ?string $title = 'Updates / Announcements';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('status')
                ->options([
                    'investigating' => '🔴 Investigating',
                    'identified'    => '🟡 Identified',
                    'monitoring'    => '🔵 Monitoring',
                    'resolved'      => '🟢 Resolved',
                ])
                ->required()
                ->default('investigating'),

            Textarea::make('message')
                ->required()
                ->rows(4)
                ->placeholder('Describe what is happening and what steps are being taken...')
                ->columnSpanFull(),

            DateTimePicker::make('posted_at')
                ->default(now())
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('message')
            ->defaultSort('posted_at', 'desc')
            ->columns([
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger'  => 'investigating',
                        'warning' => 'identified',
                        'info'    => 'monitoring',
                        'success' => 'resolved',
                    ]),

                Tables\Columns\TextColumn::make('message')
                    ->limit(80)
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('posted_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('Post update'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
