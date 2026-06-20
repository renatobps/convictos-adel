<?php

namespace App\Filament\Resources\Inscricaos\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class InscricaoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome completo')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required(),
                TextInput::make('whatsapp')
                    ->label('WhatsApp')
                    ->tel(),
                TextInput::make('idade')
                    ->label('Idade'),
                TextInput::make('cidade')
                    ->label('Cidade'),
                TextInput::make('igreja')
                    ->label('Igreja')
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'novo' => 'Novo',
                        'contatado' => 'Contatado',
                        'confirmado' => 'Confirmado',
                        'cancelado' => 'Cancelado',
                    ])
                    ->default('novo')
                    ->required(),
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
