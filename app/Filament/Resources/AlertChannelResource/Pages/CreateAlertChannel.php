<?php

namespace App\Filament\Resources\AlertChannelResource\Pages;

use App\Filament\Resources\AlertChannelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlertChannel extends CreateRecord
{
    protected static string $resource = AlertChannelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure config is always an array, never null
        $data['config'] = array_filter($data['config'] ?? [], fn ($v) => $v !== null && $v !== '');

        return $data;
    }
}
