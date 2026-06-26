<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnqueteResposta extends Model
{
    protected $fillable = [
        'enquete_id',
        'destinatario',
        'resposta',
    ];

    public function enquete(): BelongsTo
    {
        return $this->belongsTo(Enquete::class);
    }
}
