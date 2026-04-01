<?php

namespace App\Filament\Resources\StatusPageCategoryResource\Pages;

use App\Filament\Resources\StatusPageCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusPageCategory extends EditRecord
{
    protected static string $resource = StatusPageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
