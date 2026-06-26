<?php

namespace App\Filament\Resources\Enquetes\Schemas;

use App\Models\NotificacaoGrupo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EnqueteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('titulo')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                Textarea::make('pergunta')
                    ->label('Pergunta')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
                TagsInput::make('opcoes')
                    ->label('Opções de resposta (botões)')
                    ->placeholder('Digite e pressione Enter')
                    ->required()
                    ->helperText('Mínimo 2 e máximo 3 opções — cada uma vira um botão clicável no WhatsApp (máx. 20 caracteres por botão).')
                    ->rules(['array', 'min:2', 'max:3'])
                    ->columnSpanFull(),
                Select::make('notificacao_grupo_id')
                    ->label('Grupo padrão (opcional)')
                    ->relationship('grupo', 'nome')
                    ->searchable()
                    ->preload()
                    ->helperText('Usado como sugestão ao enviar. O destino pode ser alterado no momento do envio.'),
                Toggle::make('ativa')
                    ->label('Enquete ativa')
                    ->default(true),
                Textarea::make('mensagem_agradecimento')
                    ->label('Mensagem de agradecimento (WhatsApp)')
                    ->rows(3)
                    ->placeholder('Obrigado pela sua resposta! ✅ Registramos: *{resposta}*')
                    ->helperText('Enviada automaticamente após cada resposta. Variáveis: {resposta}, {nome}, {pergunta}, {titulo}. Deixe vazio para usar o padrão do sistema.')
                    ->columnSpanFull(),
            ]);
    }
}
