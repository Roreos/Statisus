<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password',
        'avatar_url', 'job_title', 'timezone', 'is_root',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_root'           => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Any user with admin or viewer role can access the panel
        // (guests are handled by Filament's auth middleware on protected pages)
        return $this->hasAnyRole(['admin', 'viewer']);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->is_root;
    }

    public function isRoot(): bool
    {
        return (bool) $this->is_root;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url
            ? asset('storage/' . $this->avatar_url)
            : null;
    }
}
