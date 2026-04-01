<?php

namespace App\Filament\Resources\AlertChannelResource\Pages;

use App\Filament\Resources\AlertChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertChannel extends EditRecord
{
    protected static string $resource = AlertChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['config'] = array_filter($data['config'] ?? [], fn ($v) => $v !== null && $v !== '');

        return $data;
    }
}
