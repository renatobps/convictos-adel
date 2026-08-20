<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Services\MercadoPagoService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class RelatorioVendas extends \Filament\Pages\Page
{
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Relatório de vendas';

    protected static ?string $title = 'Relatório de vendas';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Loja';

    protected string $view = 'filament.pages.relatorio-vendas';

    protected Width|string|null $maxContentWidth = Width::Full;

    public string $filtro_periodo = '30';

    public ?string $filtro_payment_status = null;

    public ?string $filtro_status = null;

    public int $perPage = 15;

    public ?int $detalheOrderId = null;

    /** @var array<string, mixed>|null */
    public ?array $detalheMp = null;

    public bool $carregandoMp = false;

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroPeriodo(): void
    {
        $this->resetPage();
        $this->fecharDetalhe();
    }

    public function updatedFiltroPaymentStatus(): void
    {
        $this->resetPage();
        $this->fecharDetalhe();
    }

    public function updatedFiltroStatus(): void
    {
        $this->resetPage();
        $this->fecharDetalhe();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->filtro_periodo = '30';
        $this->filtro_payment_status = null;
        $this->filtro_status = null;
        $this->resetPage();
        $this->fecharDetalhe();
    }

    public function abrirDetalhe(int $orderId, MercadoPagoService $mercadoPago): void
    {
        $order = Order::query()->with('items')->find($orderId);
        if (! $order) {
            return;
        }

        $this->detalheOrderId = $orderId;
        $this->detalheMp = null;
        $this->carregandoMp = true;

        try {
            if ($mercadoPago->isConfigured() && filled($order->payment_id)) {
                $this->detalheMp = $mercadoPago->fetchSaleDetails($order);
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Não foi possível consultar o Mercado Pago')
                ->body('Exibindo apenas os dados locais do pedido.')
                ->warning()
                ->send();
        } finally {
            $this->carregandoMp = false;
        }

        unset($this->pedidoDetalhe);
    }

    public function fecharDetalhe(): void
    {
        $this->detalheOrderId = null;
        $this->detalheMp = null;
        $this->carregandoMp = false;
        unset($this->pedidoDetalhe);
    }

    #[Computed]
    public function resumo(): array
    {
        $base = $this->baseQuery();

        $aprovados = (clone $base)->where('payment_status', 'approved');
        $pendentes = (clone $base)->whereIn('payment_status', ['pending', 'in_process', 'action_required']);
        $recusados = (clone $base)->whereIn('payment_status', ['rejected', 'cancelled', 'canceled']);

        $qtdAprovados = (clone $aprovados)->count();
        $totalAprovado = (float) (clone $aprovados)->sum('total');
        $qtdTotal = (clone $base)->count();
        $totalGeral = (float) (clone $base)->sum('total');

        return [
            'qtd_total' => $qtdTotal,
            'qtd_aprovados' => $qtdAprovados,
            'qtd_pendentes' => (clone $pendentes)->count(),
            'qtd_recusados' => (clone $recusados)->count(),
            'total_aprovado' => $totalAprovado,
            'total_geral' => $totalGeral,
            'ticket_medio' => $qtdAprovados > 0 ? $totalAprovado / $qtdAprovados : 0.0,
            'mp_configurado' => app(MercadoPagoService::class)->isConfigured(),
            'sandbox' => app(MercadoPagoService::class)->isSandbox(),
        ];
    }

    #[Computed]
    public function vendas(): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with('items')
            ->latest('id')
            ->paginate($this->perPage);
    }

    #[Computed]
    public function pedidoDetalhe(): ?Order
    {
        if (! $this->detalheOrderId) {
            return null;
        }

        return Order::query()->with('items')->find($this->detalheOrderId);
    }

    #[Computed]
    public function porMetodo(): array
    {
        return $this->baseQuery()
            ->where('payment_status', 'approved')
            ->selectRaw("COALESCE(payment_method, 'manual') as metodo, COUNT(*) as qtd, SUM(total) as total")
            ->groupByRaw("COALESCE(payment_method, 'manual')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'metodo' => $row->metodo,
                'qtd' => (int) $row->qtd,
                'total' => (float) $row->total,
            ])
            ->all();
    }

    protected function baseQuery()
    {
        $query = Order::query();

        $dias = (int) $this->filtro_periodo;
        if ($dias > 0) {
            $query->where('created_at', '>=', now()->subDays($dias)->startOfDay());
        }

        if (filled($this->filtro_payment_status)) {
            $query->where('payment_status', $this->filtro_payment_status);
        }

        if (filled($this->filtro_status)) {
            $query->where('status', $this->filtro_status);
        }

        return $query;
    }

    public function labelStatusPedido(?string $status): string
    {
        return Order::STATUSES[$status] ?? ($status ?: '—');
    }

    public function labelPagamento(?string $status): string
    {
        return match ($status) {
            'approved' => 'Aprovado',
            'pending', 'in_process', 'action_required' => 'Pendente',
            'rejected' => 'Recusado',
            'cancelled', 'canceled' => 'Cancelado',
            'error' => 'Erro',
            default => $status ?: '—',
        };
    }

    public function formatMoney(float|int|string|null $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }
}
