<?php

namespace App\Filament\Resources\InvitationResource\Pages;

use App\Filament\Resources\InvitationResource;
use App\Models\Invitation;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateInvitation extends CreateRecord
{
    protected static string $resource = InvitationResource::class;

    protected function handleRecordCreation(array $data): Invitation
    {
        // Generate handles delete + insert atomically, bypassing Filament's own create
        $invitation = Invitation::generate($data['email'], $data['role'], Auth::id());

        Notification::make()
            ->title('Invitation created')
            ->body('Link: ' . $invitation->acceptUrl())
            ->persistent()
            ->success()
            ->send();

        return $invitation;
    }
}
