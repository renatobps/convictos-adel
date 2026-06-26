<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cargo extends Model
{
    protected $table = 'cargos';

    protected $fillable = [
        'nome',
    ];

    public function membros(): HasMany
    {
        return $this->hasMany(Membro::class);
    }
}
