<?php

namespace App\Filament\Resources\NotificacaoGrupos\Schemas;

use App\Models\Igreja;
use App\Models\Inscricao;
use App\Models\NotificacaoGrupo;
use App\Models\Regional;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NotificacaoGrupoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do grupo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('tipo')
                    ->label('Tipo de grupo')
                    ->options(NotificacaoGrupo::tipoOptions())
                    ->live()
                    ->required()
                    ->disabled(fn (?NotificacaoGrupo $record) => (bool) $record?->sistema),
                Select::make('igreja_id')
                    ->label('Igreja')
                    ->options(fn () => Igreja::query()->orderBy('bairro')->pluck('bairro', 'id'))
                    ->searchable()
                    ->required(fn ($get) => $get('tipo') === NotificacaoGrupo::TIPO_IGREJA)
                    ->visible(fn ($get) => $get('tipo') === NotificacaoGrupo::TIPO_IGREJA)
                    ->disabled(fn (?NotificacaoGrupo $record) => (bool) $record?->sistema),
                Select::make('regional_id')
                    ->label('Regional')
                    ->options(fn () => Regional::query()->orderBy('nome')->pluck('nome', 'id'))
                    ->searchable()
                    ->required(fn ($get) => $get('tipo') === NotificacaoGrupo::TIPO_REGIONAL)
                    ->visible(fn ($get) => $get('tipo') === NotificacaoGrupo::TIPO_REGIONAL)
                    ->disabled(fn (?NotificacaoGrupo $record) => (bool) $record?->sistema),
                Select::make('status_inscricao')
                    ->label('Status dos inscritos')
                    ->options([
                        '' => 'Todos',
                        ...Inscricao::statusOptions(),
                    ])
                    ->visible(fn ($get) => $get('tipo') === NotificacaoGrupo::TIPO_INSCRITOS)
                    ->disabled(fn (?NotificacaoGrupo $record) => (bool) $record?->sistema),
                Toggle::make('sistema')
                    ->label('Grupo do sistema')
                    ->disabled()
                    ->visible(fn (?NotificacaoGrupo $record) => $record !== null),
            ]);
    }
}
