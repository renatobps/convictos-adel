<?php

namespace App\Filament\Resources\AtividadeLogs\Pages;

use App\Filament\Resources\AtividadeLogs\AtividadeLogResource;
use Filament\Resources\Pages\ListRecords;

class ListAtividadeLogs extends ListRecords
{
    protected static string $resource = AtividadeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
