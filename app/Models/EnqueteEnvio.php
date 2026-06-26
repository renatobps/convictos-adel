<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnqueteEnvio extends Model
{
    protected $fillable = [
        'enquete_id',
        'destinatario',
        'nome_destinatario',
        'status',
    ];

    public function enquete(): BelongsTo
    {
        return $this->belongsTo(Enquete::class);
    }
}
