<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function membroPorEmail(): HasOne
    {
        return $this->hasOne(Membro::class, 'email', 'email');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->membroVinculado()?->fotoUrl();
    }

    public function membroVinculado(): ?Membro
    {
        $email = mb_strtolower(trim((string) $this->email));
        if ($email === '') {
            return null;
        }

        return Membro::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessAdminPanel();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    /**
     * @return array<int, int>
     */
    public function regionalScopeIds(): array
    {
        if ($this->isAdmin()) {
            return [];
        }

        $membro = $this->membroVinculado()?->loadMissing('acessosRegionais');

        if (! $membro) {
            return [];
        }

        return $membro->acessosRegionais
            ->pluck('regional_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function isRegionalLeader(): bool
    {
        return ! $this->isAdmin() && count($this->regionalScopeIds()) > 0;
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->isAdmin() || $this->isRegionalLeader();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }
}
