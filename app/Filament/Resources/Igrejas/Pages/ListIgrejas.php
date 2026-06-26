<?php

namespace App\Filament\Resources\Igrejas\Pages;

use App\Filament\Resources\Igrejas\IgrejaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIgrejas extends ListRecords
{
    protected static string $resource = IgrejaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
