<?php

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewIncident extends ViewRecord
{
    protected static string $resource = IncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('post_update')
                ->label('Post update')
                ->icon('heroicon-o-chat-bubble-left-ellipsis')
                ->color('warning')
                ->url(fn () => '#updates'),
        ];
    }
}
