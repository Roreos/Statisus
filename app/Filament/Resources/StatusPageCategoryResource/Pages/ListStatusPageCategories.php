<?php

namespace App\Filament\Resources\StatusPageCategoryResource\Pages;

use App\Filament\Resources\StatusPageCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusPageCategories extends ListRecords
{
    protected static string $resource = StatusPageCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
