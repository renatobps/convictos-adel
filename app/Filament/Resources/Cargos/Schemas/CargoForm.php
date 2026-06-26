<?php

namespace App\Filament\Resources\Cargos\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CargoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nome')
                    ->label('Nome do cargo')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }
}
