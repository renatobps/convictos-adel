<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Enquetes\EnqueteResource;
use App\Filament\Resources\NotificacaoGrupos\NotificacaoGrupoResource;
use App\Models\Enquete;
use App\Models\NotificacaoEnviada;
use App\Models\NotificacaoGrupo;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class Notificacoes extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Notificações';

    protected static ?string $title = 'Notificações';

    protected static ?int $navigationSort = 5;

    protected static bool $shouldRegisterNavigation = false;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    protected string $view = 'filament.pages.notificacoes-hub';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getViewData(): array
    {
        return [
            'totalGrupos' => NotificacaoGrupo::query()->count(),
            'totalEnquetes' => Enquete::query()->count(),
            'enviosHoje' => NotificacaoEnviada::query()->whereDate('created_at', today())->count(),
            'links' => [
                [
                    'label' => 'Grupos',
                    'desc' => 'Grupos por igreja, regional e inscritos',
                    'url' => NotificacaoGrupoResource::getUrl(),
                    'icon' => 'heroicon-o-user-group',
                ],
                [
                    'label' => 'Enquetes',
                    'desc' => 'Criar e enviar enquetes por WhatsApp',
                    'url' => EnqueteResource::getUrl(),
                    'icon' => 'heroicon-o-clipboard-document-list',
                ],
                [
                    'label' => 'Painel',
                    'desc' => 'Envio de mensagens e histórico',
                    'url' => NotificacoesPainel::getUrl(),
                    'icon' => 'heroicon-o-paper-airplane',
                ],
                [
                    'label' => 'Templates',
                    'desc' => 'Mensagens automáticas e modelos',
                    'url' => NotificacoesTemplates::getUrl(),
                    'icon' => 'heroicon-o-document-text',
                ],
                [
                    'label' => 'Configuração WPP',
                    'desc' => 'Conexão WhatsApp e testes',
                    'url' => WhatsAppConfiguracao::getUrl(),
                    'icon' => 'heroicon-o-qr-code',
                ],
                [
                    'label' => 'Email',
                    'desc' => 'Configurar envio e e-mails de inscrição',
                    'url' => EmailConfiguracao::getUrl(),
                    'icon' => 'heroicon-o-envelope',
                ],
            ],
        ];
    }
}
