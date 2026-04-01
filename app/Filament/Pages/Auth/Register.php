<?php

namespace App\Filament\Pages\Auth;

use App\Models\Invitation;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Livewire\Attributes\Url;

class Register extends BaseRegister
{
    #[Url]
    public string $token = '';

    protected ?Invitation $invitation = null;

    public function mount(): void
    {
        if (!$this->token) {
            abort(403, 'Registration requires an invitation.');
        }

        $this->invitation = Invitation::where('token', $this->token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$this->invitation) {
            abort(403, 'This invitation is invalid or has expired.');
        }

        parent::mount();
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Full name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->default($this->invitation?->email)
                ->disabled()
                ->dehydrated()
                ->required()
                ->email(),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->revealable()
                ->required()
                ->minLength(8)
                ->confirmed(),

            TextInput::make('password_confirmation')
                ->label('Confirm password')
                ->password()
                ->revealable()
                ->required(),
        ]);
    }

    protected function handleRegistration(array $data): \App\Models\User
    {
        // Re-fetch invitation in case it was lost between requests
        $invitation = Invitation::where('token', $this->token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        /** @var \App\Models\User $user */
        $user = parent::handleRegistration($data);

        $user->assignRole($invitation->role);
        $invitation->update(['accepted_at' => now()]);

        return $user;
    }
}
