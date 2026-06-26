<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Regional extends Model
{
    protected $table = 'regionais';

    protected $fillable = [
        'nome',
        'pastor_responsavel',
    ];

    public function igrejas(): HasMany
    {
        return $this->hasMany(Igreja::class);
    }

    public function label(): string
    {
        return $this->nome.' — '.$this->pastor_responsavel;
    }

    /**
     * Versão curta do nome da regional (ex.: "REGIONAL 1" -> "R1").
     */
    public function abreviacao(): string
    {
        if (preg_match('/(\d+)/', (string) $this->nome, $m)) {
            return 'R'.$m[1];
        }

        return Str::of($this->nome)
            ->explode(' ')
            ->filter()
            ->map(fn (string $palavra): string => Str::upper(Str::substr($palavra, 0, 1)))
            ->implode('');
    }
}
