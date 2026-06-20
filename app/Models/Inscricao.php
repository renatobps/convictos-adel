<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscricao extends Model
{
    protected $table = 'inscricoes';

    protected $fillable = [
        'nome',
        'email',
        'whatsapp',
        'idade',
        'igreja',
        'cidade',
        'status',
        'observacoes',
    ];
}
