<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtividadeLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'atividade_logs';

    protected $fillable = [
        'user_id',
        'usuario_nome',
        'usuario_email',
        'descricao',
        'acao',
        'entidade_tipo',
        'entidade_id',
        'detalhes',
    ];

    protected function casts(): array
    {
        return [
            'detalhes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
