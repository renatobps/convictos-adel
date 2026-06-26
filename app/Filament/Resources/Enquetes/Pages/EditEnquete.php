<?php

namespace App\Filament\Resources\Enquetes\Pages;

use App\Filament\Resources\Enquetes\EnqueteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnquete extends EditRecord
{
    protected static string $resource = EnqueteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
