<?php

namespace App\Filament\Resources\Regionals\Schemas;

use App\Models\Membro;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RegionalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome da regional')
                    ->required()
                    ->maxLength(255),
                Select::make('pastor_membro_id')
                    ->label('Pastor responsável')
                    ->options(fn (): array => Membro::query()->orderBy('nome')->pluck('nome', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->placeholder('Selecione o pastor…'),
            ]);
    }
}
