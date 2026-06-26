<?php

namespace App\Filament\Resources\Membros\Pages;

use App\Filament\Resources\Membros\MembroResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMembro extends EditRecord
{
    protected static string $resource = MembroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
