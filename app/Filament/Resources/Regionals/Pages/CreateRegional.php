<?php

namespace App\Filament\Resources\Regionals\Pages;

use App\Filament\Resources\Regionals\RegionalResource;
use App\Models\Membro;
use Filament\Resources\Pages\CreateRecord;

class CreateRegional extends CreateRecord
{
    protected static string $resource = RegionalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $membro = Membro::query()->findOrFail($data['pastor_membro_id']);

        $data['pastor_responsavel'] = $membro->nome;
        unset($data['pastor_membro_id']);

        return $data;
    }
}
