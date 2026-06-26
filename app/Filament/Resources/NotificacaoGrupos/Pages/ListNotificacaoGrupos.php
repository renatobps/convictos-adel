<?php

namespace App\Filament\Resources\NotificacaoGrupos\Pages;

use App\Filament\Resources\NotificacaoGrupos\NotificacaoGrupoResource;
use Database\Seeders\NotificacaoGrupoSeeder;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNotificacaoGrupos extends ListRecords
{
    protected static string $resource = NotificacaoGrupoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sincronizar')
                ->label('Sincronizar grupos')
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    (new NotificacaoGrupoSeeder)->run();
                    \Filament\Notifications\Notification::make()
                        ->title('Grupos sincronizados com igrejas, regionais e inscritos.')
                        ->success()
                        ->send();
                }),
            CreateAction::make(),
        ];
    }
}
