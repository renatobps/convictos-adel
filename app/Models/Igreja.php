<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Igreja extends Model
{
    protected $table = 'igrejas';

    protected $fillable = [
        'bairro',
        'dirigente',
        'dirigente_membro_id',
        'regional_id',
    ];

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }

    public function dirigenteMembro(): BelongsTo
    {
        return $this->belongsTo(Membro::class, 'dirigente_membro_id');
    }

    public function nomeNoFormulario(): string
    {
        $regional = $this->relationLoaded('regional') ? $this->regional?->nome : null;

        return $regional
            ? $this->bairro.' ('.$regional.')'
            : $this->bairro;
    }
}
