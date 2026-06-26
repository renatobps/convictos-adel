<?php

namespace App\Filament\Resources\Enquetes\Pages;

use App\Filament\Resources\Enquetes\EnqueteResource;
use App\Models\Enquete;
use App\Services\EnqueteService;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;

class ListEnquetes extends ListRecords
{
    protected static string $resource = EnqueteResource::class;

    #[Url(as: 'enquete')]
    public ?int $dashboardEnqueteId = null;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function mount(): void
    {
        parent::mount();

        if ($this->dashboardEnqueteId === null) {
            $this->dashboardEnqueteId = Enquete::query()->latest('id')->value('id');
        }
    }

    public function updatedDashboardEnqueteId(): void
    {
        $this->resetPage('dashboardRespostasPage');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getTabsContentComponent(),
                View::make('filament.resources.enquetes.list-enquetes-dashboard'),
                EmbeddedTable::make(),
            ]);
    }

    /** @return Collection<int, Enquete> */
    public function enquetesParaFiltro(): Collection
    {
        return Enquete::query()
            ->orderByDesc('id')
            ->get(['id', 'titulo', 'ativa']);
    }

    public function enqueteSelecionada(): ?Enquete
    {
        if ($this->dashboardEnqueteId === null) {
            return null;
        }

        return Enquete::query()->find($this->dashboardEnqueteId);
    }

    /** @return array{totalRespostas: int, totalEnvios: int, taxa: float, metricas: array<string, int>} */
    public function dashboardStats(): array
    {
        $enquete = $this->enqueteSelecionada();

        if ($enquete === null) {
            return [
                'totalRespostas' => 0,
                'totalEnvios' => 0,
                'taxa' => 0.0,
                'metricas' => [],
            ];
        }

        $totalRespostas = $enquete->respostas()->count();
        $totalEnvios = $enquete->envios()->where('status', 'enviada')->count();

        return [
            'totalRespostas' => $totalRespostas,
            'totalEnvios' => $totalEnvios,
            'taxa' => $totalEnvios > 0 ? round(($totalRespostas / $totalEnvios) * 100, 1) : 0.0,
            'metricas' => EnqueteService::metricasRespostas($enquete),
        ];
    }

    public function dashboardRespostas(): LengthAwarePaginator
    {
        $enquete = $this->enqueteSelecionada();

        if ($enquete === null) {
            return new LengthAwarePaginator([], 0, 5, 1, [
                'pageName' => 'dashboardRespostasPage',
            ]);
        }

        return $enquete->respostas()
            ->latest()
            ->paginate(5, ['*'], 'dashboardRespostasPage');
    }
}
