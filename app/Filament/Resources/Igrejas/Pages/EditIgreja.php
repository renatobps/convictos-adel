<?php

namespace App\Filament\Resources\Igrejas\Pages;

use App\Filament\Resources\Igrejas\IgrejaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIgreja extends EditRecord
{
    protected static string $resource = IgrejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
