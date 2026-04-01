<?php

namespace App\Models;

use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use MassPrunable;

    protected $fillable = ['email', 'role', 'token', 'invited_by', 'accepted_at', 'expires_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at'  => 'datetime',
    ];

    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::where(function ($q) {
            $q->whereNotNull('accepted_at')->where('accepted_at', '<', now()->subDays(30));
        })->orWhere(function ($q) {
            $q->whereNull('accepted_at')->where('expires_at', '<', now()->subDays(7));
        });
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public function isPending(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    public static function generate(string $email, string $role, int $invitedBy): self
    {
        // Delete any existing invite for this email first to avoid unique token conflicts
        static::where('email', $email)->delete();

        return static::create([
            'email'      => $email,
            'role'       => $role,
            'token'      => Str::random(64),
            'invited_by' => $invitedBy,
            'expires_at' => now()->addDays(7),
        ]);
    }

    public function acceptUrl(): string
    {
        return route('invitation.accept', ['token' => $this->token]);
    }
}
