<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\RestrictsByRegional;
use App\Support\InscricaoDashboardStats;
use BackedEnum;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

class RelatorioInscricoes extends \Filament\Pages\Page
{
    use RestrictsByRegional;
    use WithPagination;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Relatório de inscrições';

    protected static ?string $title = 'Relatório de inscrições';

    protected static ?int $navigationSort = 0;

    protected static string|\UnitEnum|null $navigationGroup = 'Conferência';

    protected string $view = 'filament.pages.relatorio-inscricoes';

    protected Width|string|null $maxContentWidth = Width::Full;

    public ?int $filtro_regional_id = null;

    public ?int $filtro_igreja_id = null;

    public int $perPage = 10;

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updatedFiltroRegionalId(): void
    {
        $this->filtro_igreja_id = null;
        $this->resetPage();
    }

    public function updatedFiltroIgrejaId(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function limparFiltros(): void
    {
        $this->filtro_regional_id = null;
        $this->filtro_igreja_id = null;
        $this->resetPage();
    }

    #[Computed]
    public function stats(): InscricaoDashboardStats
    {
        return InscricaoDashboardStats::forUser(
            Auth::user(),
            $this->filtro_regional_id,
            $this->filtro_igreja_id,
        );
    }

    #[Computed]
    public function resumo(): array
    {
        return $this->stats->resumo();
    }

    #[Computed]
    public function regionaisCards(): array
    {
        return $this->stats->regionaisCards();
    }

    #[Computed]
    public function metasRegionais(): array
    {
        return $this->stats->metasRegionais();
    }

    #[Computed]
    public function igrejasPorRegional(): array
    {
        return $this->stats->igrejasPorRegional();
    }

    #[Computed]
    public function regionaisFiltro()
    {
        return $this->stats->regionaisFiltro();
    }

    #[Computed]
    public function igrejasFiltro()
    {
        return $this->stats->igrejasFiltro();
    }

    #[Computed]
    public function inscricoesRecentes(): LengthAwarePaginator
    {
        return $this->stats->inscricoesRecentes($this->perPage);
    }
}
