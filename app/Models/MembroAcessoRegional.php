<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MembroAcessoRegional extends Model
{
    protected $table = 'membro_acesso_regionais';

    protected $fillable = [
        'membro_id',
        'regional_id',
    ];

    public function membro(): BelongsTo
    {
        return $this->belongsTo(Membro::class);
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }
}
