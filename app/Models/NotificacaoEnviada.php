<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacaoEnviada extends Model
{
    protected $table = 'notificacoes_enviadas';

    protected $fillable = [
        'destinatario',
        'mensagem',
        'status',
        'tipo_envio',
        'notificacao_grupo_id',
        'inscricao_id',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(NotificacaoGrupo::class, 'notificacao_grupo_id');
    }

    public function inscricao(): BelongsTo
    {
        return $this->belongsTo(Inscricao::class);
    }
}
