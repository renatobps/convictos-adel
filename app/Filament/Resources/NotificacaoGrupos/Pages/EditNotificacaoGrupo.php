<?php

namespace App\Filament\Resources\NotificacaoGrupos\Pages;

use App\Filament\Resources\NotificacaoGrupos\NotificacaoGrupoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditNotificacaoGrupo extends EditRecord
{
    protected static string $resource = NotificacaoGrupoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(! $this->record->sistema),
        ];
    }
}
