<?php

namespace App\Filament\Pages;

use App\Services\AtividadeLogService;
use App\Services\WhatsAppService;
use App\Support\NotificacaoHistorico;
use App\Support\WhatsAppAtividades;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;

class WhatsAppConfiguracao extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'Configuração WPP';

    protected static ?string $title = 'Configuração WPP';

    protected static ?int $navigationSort = 5;

    protected static string|\UnitEnum|null $navigationGroup = 'Notificações';

    protected string $view = 'filament.pages.whats-app-configuracao';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?array $wppInfo = null;

    public string $numero_teste = '61993640457';

    public string $mensagem_teste = 'Teste do sistema Convictos UM 2027!';

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->carregarStatus('status');
    }

    public function verificarStatus(): void
    {
        $this->carregarStatus('status');
        $ok = empty($this->wppInfo['erros'] ?? []);
        WhatsAppAtividades::registrar(
            'status',
            $ok ? 'Status verificado' : 'Erro ao verificar status',
            $ok ? 'ok' : 'erro'
        );
        $this->limparCacheComputado();
    }

    public function obterQrCode(): void
    {
        $this->carregarStatus('qr');
        $qr = (string) ($this->wppInfo['qrCode'] ?? '');
        $ok = $qr !== '' || $this->conectado;
        WhatsAppAtividades::registrar(
            'qrcode',
            $qr !== '' ? 'QR Code obtido' : ($this->conectado ? 'Instância já conectada' : 'Erro ao gerar QR Code'),
            $ok ? 'ok' : 'erro'
        );
        $this->limparCacheComputado();
    }

    public function desconectarEGerarQr(): void
    {
        $service = app(WhatsAppService::class);

        if (! $service->desconectarInstancia()) {
            Notification::make()->title('Não foi possível desconectar a instância.')->danger()->send();

            return;
        }

        sleep(2);
        $this->carregarStatus('qr');

        if (filled($this->wppInfo['qrCode'] ?? '')) {
            Notification::make()->title('QR Code gerado. Escaneie com o WhatsApp.')->success()->send();
            WhatsAppAtividades::registrar('qrcode', 'Sessão desconectada — QR Code gerado', 'ok');
            AtividadeLogService::registrar(
                'Desconectou WhatsApp e gerou novo QR Code',
                AtividadeLogService::ACAO_CONFIG,
            );
        } else {
            Notification::make()->title('Desconectado. Clique em "Obter QR Code" novamente se o QR não aparecer.')->warning()->send();
            WhatsAppAtividades::registrar('qrcode', 'Sessão desconectada', 'ok');
            AtividadeLogService::registrar(
                'Desconectou sessão do WhatsApp',
                AtividadeLogService::ACAO_CONFIG,
            );
        }

        $this->limparCacheComputado();
    }

    public function carregarStatus(string $action = 'status'): void
    {
        $this->wppInfo = app(WhatsAppService::class)->obterStatusInstancia($action);
        $this->limparCacheComputado();
    }

    protected function limparCacheComputado(): void
    {
        unset($this->conectado, $this->qrCode, $this->qrMensagem, $this->pairingCode, $this->erros, $this->atividades, $this->dadosInstancia);
    }

    #[Computed]
    public function conectado(): bool
    {
        if ($this->wppInfo === null) {
            return false;
        }

        return app(WhatsAppService::class)->instanciaConectada(
            $this->wppInfo['status'] ?? null,
            $this->wppInfo['instanceInfo'] ?? null,
        );
    }

    /**
     * @return array{nome: string, perfil: ?string, numero: ?string}
     */
    #[Computed]
    public function dadosInstancia(): array
    {
        $service = app(WhatsAppService::class);

        if ($this->wppInfo === null) {
            return [
                'nome' => $service->nomeInstanciaConfigurada(),
                'perfil' => null,
                'numero' => null,
            ];
        }

        return $service->obterDadosInstancia(
            $this->wppInfo['status'] ?? null,
            $this->wppInfo['instanceInfo'] ?? null,
        );
    }

    #[Computed]
    public function qrCode(): string
    {
        return (string) ($this->wppInfo['qrCode'] ?? '');
    }

    #[Computed]
    public function qrMensagem(): string
    {
        return (string) ($this->wppInfo['qrMensagem'] ?? '');
    }

    #[Computed]
    public function pairingCode(): string
    {
        return (string) ($this->wppInfo['pairingCode'] ?? '');
    }

    /**
     * @return array<int, string>
     */
    #[Computed]
    public function erros(): array
    {
        return $this->wppInfo['erros'] ?? [];
    }

    /**
     * @return array<int, array{hora: string, tipo: string, texto: string, status: string, destinatario: string|null}>
     */
    #[Computed]
    public function atividades(): array
    {
        return WhatsAppAtividades::listar(12);
    }

    public function enviarTeste(): void
    {
        $whatsApp = app(WhatsAppService::class);
        $numero = $whatsApp->normalizarNumeroWhatsapp($this->numero_teste);

        if ($numero === null) {
            Notification::make()->title('Número inválido. Use DDD + número (ex.: 61993640457).')->danger()->send();

            return;
        }

        $mensagem = trim($this->mensagem_teste);
        $ok = $whatsApp->enviarTexto($numero, $mensagem);

        NotificacaoHistorico::registrar($numero, $mensagem, $ok ? 'enviada' : 'erro');

        if ($ok) {
            AtividadeLogService::registrar(
                'Enviou mensagem de teste WhatsApp para '.$numero,
                AtividadeLogService::ACAO_NOTIFICACAO,
            );
            Notification::make()->title('Mensagem de teste enviada.')->success()->send();
        } else {
            $erro = $whatsApp->obterUltimoErro() ?: 'Falha ao enviar mensagem de teste.';
            Notification::make()->title($erro)->danger()->send();
        }

        $this->limparCacheComputado();
    }
}
