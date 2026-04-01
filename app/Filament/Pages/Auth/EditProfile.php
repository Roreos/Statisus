<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;

class EditProfile extends BaseEditProfile
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')
                ->description('Update your name, avatar and personal details.')
                ->columns(2)
                ->schema([
                    FileUpload::make('avatar_url')
                        ->label('Avatar')
                        ->image()
                        ->avatar()
                        ->disk('public')
                        ->directory('avatars')
                        ->columnSpanFull(),

                    $this->getNameFormComponent()
                        ->columnSpanFull(),

                    TextInput::make('job_title')
                        ->label('Job title')
                        ->maxLength(100),

                    Select::make('timezone')
                        ->label('Timezone')
                        ->options(
                            collect(timezone_identifiers_list())
                                ->mapWithKeys(fn ($tz) => [$tz => $tz])
                                ->toArray()
                        )
                        ->searchable()
                        ->default('UTC'),
                ]),

            Section::make('Email address')
                ->description('Changing your email will require re-verification.')
                ->schema([
                    $this->getEmailFormComponent(),
                ]),

            Section::make('Password')
                ->description('Leave blank to keep your current password.')
                ->schema([
                    $this->getPasswordFormComponent(),
                    $this->getPasswordConfirmationFormComponent(),
                    $this->getCurrentPasswordFormComponent(),
                ]),
        ]);
    }
}
