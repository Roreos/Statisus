<?php

namespace App\Filament\Resources\MaintenanceWindowResource\Pages;

use App\Filament\Resources\MaintenanceWindowResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceWindow extends EditRecord
{
    protected static string $resource = MaintenanceWindowResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
