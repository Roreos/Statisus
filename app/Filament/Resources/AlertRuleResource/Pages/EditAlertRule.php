<?php

namespace App\Filament\Resources\AlertRuleResource\Pages;

use App\Filament\Resources\AlertRuleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAlertRule extends EditRecord
{
    protected static string $resource = AlertRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['primary_channel_ids'] = $this->record->primaryChannels()->pluck('alert_channels.id')->toArray();
        $data['escalation_channel_ids'] = $this->record->escalationChannels()->pluck('alert_channels.id')->toArray();

        return $data;
    }

    protected function afterSave(): void
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
