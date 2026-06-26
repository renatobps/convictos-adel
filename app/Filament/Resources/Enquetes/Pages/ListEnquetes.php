<?php

namespace App\Filament\Resources\Enquetes\Pages;

use App\Filament\Resources\Enquetes\EnqueteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnquetes extends ListRecords
{
    protected static string $resource = EnqueteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
