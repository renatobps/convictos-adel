<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enquete extends Model
{
    protected $table = 'notificacao_enquetes';

    protected $fillable = [
        'titulo',
        'pergunta',
        'opcoes',
        'ativa',
        'notificacao_grupo_id',
    ];

    protected function casts(): array
    {
        return [
            'opcoes' => 'array',
            'ativa' => 'boolean',
        ];
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(NotificacaoGrupo::class, 'notificacao_grupo_id');
    }

    public function envios(): HasMany
    {
        return $this->hasMany(EnqueteEnvio::class);
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(EnqueteResposta::class);
    }

    public function mensagemFormatada(): string
    {
        return $this->previewEnvioBotoes();
    }

    public function previewEnvioBotoes(): string
    {
        $opcoes = array_slice($this->opcoes ?? [], 0, 3);
        $linhas = [
            'Tipo: Botões WhatsApp',
            '',
            'Título: '.mb_substr((string) $this->titulo, 0, 30),
            'Descrição: '.mb_substr((string) $this->pergunta, 0, 120),
            '',
            'Botões:',
        ];

        foreach ($opcoes as $opcao) {
            $label = is_string($opcao) ? $opcao : (string) ($opcao['label'] ?? $opcao['name'] ?? '');
            $linhas[] = '  ['.mb_substr(trim($label), 0, 20).']';
        }

        if (count($this->opcoes ?? []) > 3) {
            $linhas[] = '';
            $linhas[] = 'Obs.: apenas as 3 primeiras opções são enviadas como botão (limite do WhatsApp).';
        }

        return implode("\n", $linhas);
    }
}
