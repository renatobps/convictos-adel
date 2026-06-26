<?php

namespace App\Filament\Resources\Membros\Pages;

use App\Filament\Resources\Membros\MembroResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembros extends ListRecords
{
    protected static string $resource = MembroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
