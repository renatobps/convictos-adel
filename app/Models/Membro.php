<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Membro extends Model
{
    protected $table = 'membros';

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'foto',
        'cargo_id',
        'telefone',
    ];

    protected $hidden = [
        'senha',
    ];

    protected function casts(): array
    {
        return [
            'senha' => 'hashed',
        ];
    }

    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function acessosRegionais(): HasMany
    {
        return $this->hasMany(MembroAcessoRegional::class);
    }

    public function possuiCredenciais(): bool
    {
        return filled($this->email) && filled($this->senhaHash());
    }

    public function fotoUrl(): ?string
    {
        if (blank($this->foto)) {
            return null;
        }

        return Storage::disk('public')->url($this->foto);
    }

    /**
     * Hash da senha já persistido no banco (sem passar pelo cast de escrita).
     */
    public function senhaHash(): ?string
    {
        $hash = $this->getRawOriginal('senha');

        return filled($hash) ? (string) $hash : null;
    }

    /**
     * Cria ou atualiza o usuário do painel com a mesma senha cadastrada no membro.
     */
    public function sincronizarUsuario(bool $promoverAdmin = false): User
    {
        if (! $this->possuiCredenciais()) {
            throw new \InvalidArgumentException('Membro sem e-mail ou senha.');
        }

        $email = mb_strtolower(trim((string) $this->email));

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first() ?? new User(['email' => $email]);

        if (! $user->exists) {
            $user->is_admin = false;
        }

        if ($promoverAdmin) {
            $user->is_admin = true;
        }

        $user->name = $this->nome;
        $user->email = $email;
        $user->save();

        // Copia o hash diretamente para evitar re-criptografia dupla no cast "hashed".
        User::query()->whereKey($user->id)->update(['password' => $this->senhaHash()]);

        return $user->fresh();
    }

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = filled($value)
            ? mb_strtolower(trim($value))
            : null;
    }

    public function setTelefoneAttribute($value): void
    {
        if (blank($value)) {
            $this->attributes['telefone'] = null;

            return;
        }

        $this->attributes['telefone'] = preg_replace('/\D+/', '', (string) $value) ?? '';
    }
}
