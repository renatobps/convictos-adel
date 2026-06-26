<?php

namespace App\Filament\Resources\Regionals\Pages;

use App\Filament\Resources\Regionals\RegionalResource;
use App\Models\Membro;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRegional extends EditRecord
{
    protected static string $resource = RegionalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (filled($data['pastor_responsavel'] ?? null)) {
            $data['pastor_membro_id'] = Membro::query()
                ->where('nome', $data['pastor_responsavel'])
                ->value('id');
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $membro = Membro::query()->findOrFail($data['pastor_membro_id']);

        $data['pastor_responsavel'] = $membro->nome;
        unset($data['pastor_membro_id']);

        return $data;
    }
}
