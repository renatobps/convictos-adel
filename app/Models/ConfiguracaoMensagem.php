<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracaoMensagem extends Model
{
    protected $table = 'configuracoes_mensagens';

    protected $fillable = [
        'chave',
        'titulo',
        'conteudo',
        'imagem_url',
    ];
}
