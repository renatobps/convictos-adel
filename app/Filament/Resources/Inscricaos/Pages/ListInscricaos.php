<?php

namespace App\Filament\Resources\Inscricaos\Pages;

use App\Filament\Resources\Inscricaos\InscricaoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

class ListInscricaos extends ListRecords
{
    protected static string $resource = InscricaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                View::make('filament.resources.inscricoes.list-inscricoes-mobile'),
                EmbeddedTable::make(),
            ]);
    }
}
