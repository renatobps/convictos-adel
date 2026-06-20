<?php

namespace App\Filament\Resources\Inscricaos\Pages;

use App\Filament\Resources\Inscricaos\InscricaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInscricao extends EditRecord
{
    protected static string $resource = InscricaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
