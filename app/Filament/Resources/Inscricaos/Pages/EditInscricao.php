<?php

namespace App\Filament\Resources\Inscricaos\Pages;

use App\Filament\Resources\Inscricaos\InscricaoResource;
use App\Models\Inscricao;
use App\Services\WhatsAppService;
use App\Support\EmailConfig;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInscricao extends EditRecord
{
    protected static string $resource = InscricaoResource::class;

    protected ?string $statusAnterior = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->statusAnterior = $data['status'] ?? null;

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Inscricao $inscricao */
        $inscricao = $this->getRecord();

        if (
            $this->statusAnterior !== Inscricao::STATUS_CONFIRMADA
            && $inscricao->status === Inscricao::STATUS_CONFIRMADA
        ) {
            app(WhatsAppService::class)->enviarConfirmacao($inscricao);
            $this->enviarEmailConfirmacao($inscricao);
        }

        $this->statusAnterior = $inscricao->status;
    }

    protected function enviarEmailConfirmacao(Inscricao $inscricao): void
    {
        EmailConfig::enviarParaInscricao($inscricao, EmailConfig::TIPO_CONFIRMADA);
    }
}
