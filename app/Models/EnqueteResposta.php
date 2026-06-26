<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnqueteResposta extends Model
{
    protected $fillable = [
        'enquete_id',
        'enquete_envio_id',
        'destinatario',
        'nome_destinatario',
        'resposta',
        'opcao_indice',
        'origem',
    ];

    public function enqueteEnvio(): BelongsTo
    {
        return $this->belongsTo(EnqueteEnvio::class);
    }

    public function enquete(): BelongsTo
    {
        return $this->belongsTo(Enquete::class);
    }
}
