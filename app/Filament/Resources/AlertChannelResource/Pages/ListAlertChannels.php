<?php

namespace App\Filament\Resources\AlertChannelResource\Pages;

use App\Filament\Resources\AlertChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAlertChannels extends ListRecords
{
    protected static string $resource = AlertChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
