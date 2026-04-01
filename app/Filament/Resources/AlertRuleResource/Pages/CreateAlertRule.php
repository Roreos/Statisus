<?php

namespace App\Filament\Resources\AlertRuleResource\Pages;

use App\Filament\Resources\AlertRuleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAlertRule extends CreateRecord
{
    protected static string $resource = AlertRuleResource::class;

    protected function afterCreate(): void
    {
        $this->syncChannels();
    }

    private function syncChannels(): void
    {
        $data = $this->form->getRawState();

        $primary = collect($data['primary_channel_ids'] ?? [])
            ->mapWithKeys(fn ($id) => [$id => ['escalation_level' => 0]])
            ->all();

        $escalation = collect($data['escalation_channel_ids'] ?? [])
            ->mapWithKeys(fn ($id) => [$id => ['escalation_level' => 1]])
            ->all();

        $this->record->channels()->sync($primary + $escalation);
    }
}
