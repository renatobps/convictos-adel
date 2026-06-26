<?php

namespace App\Filament\Resources\Membros\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class MembroForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                TextInput::make('senha')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->minLength(6),
                TextInput::make('telefone')
                    ->label('Telefone / WhatsApp')
                    ->tel()
                    ->maxLength(40),
                Select::make('cargo_id')
                    ->label('Cargo')
                    ->relationship('cargo', 'nome')
                    ->searchable()
                    ->preload(),
                FileUpload::make('foto')
                    ->label('Foto')
                    ->image()
                    ->directory('membros/fotos')
                    ->disk('public'),
            ]);
    }
}
