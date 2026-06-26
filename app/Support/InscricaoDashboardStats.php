<?php

namespace App\Support;

use App\Models\Igreja;
use App\Models\Inscricao;
use App\Models\Regional;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InscricaoDashboardStats
{
    /** @param  array<int, int>  $regionalScopeIds */
    public function __construct(
        private readonly array $regionalScopeIds,
        private readonly ?int $selectedRegionalId = null,
        private readonly ?int $selectedIgrejaId = null,
    ) {}

    public static function forUser(?User $user, ?int $regionalId = null, ?int $igrejaId = null): self
    {
        $scopeIds = $user?->regionalScopeIds() ?? [];

        $selectedRegionalId = $regionalId;
        if (! empty($scopeIds)) {
            $selectedRegionalId = $regionalId && in_array($regionalId, $scopeIds, true)
                ? $regionalId
                : null;
        }

        return new self($scopeIds, $selectedRegionalId, $igrejaId);
    }

    public function selectedRegionalId(): ?int
    {
        return $this->selectedRegionalId;
    }

    public function selectedIgrejaId(): ?int
    {
        return $this->selectedIgrejaId;
    }

    /** @return Collection<int, Regional> */
    public function regionaisFiltro(): Collection
    {
        return Regional::query()
            ->withCount('igrejas')
            ->when(! empty($this->regionalScopeIds), fn ($q) => $q->whereIn('id', $this->regionalScopeIds))
            ->orderBy('nome')
            ->get();
    }

    /** @return Collection<int, Igreja> */
    public function igrejasFiltro(): Collection
    {
        return Igreja::query()
            ->with('regional')
            ->when(! empty($this->regionalScopeIds), fn ($q) => $q->whereIn('regional_id', $this->regionalScopeIds))
            ->when($this->selectedRegionalId, fn ($q) => $q->where('regional_id', $this->selectedRegionalId))
            ->orderBy('bairro')
            ->get();
    }

    public function metaTotal(): int
    {
        return (int) (DB::table('inscricao_meta_configuracoes')->value('meta_total') ?? 500);
    }

    public function valorInscricao(): float
    {
        return (float) (DB::table('inscricao_meta_configuracoes')->value('valor_inscricao') ?? 0);
    }

    /** @return array<string, mixed> */
    public function resumo(): array
    {
        $porStatus = $this->baseQuery()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = (int) $porStatus->sum();
        $confirmadas = (int) ($porStatus[Inscricao::STATUS_CONFIRMADA] ?? 0);
        $aguardando = (int) ($porStatus[Inscricao::STATUS_AGUARDANDO] ?? 0);
        $canceladas = (int) ($porStatus[Inscricao::STATUS_CANCELADA] ?? 0);
        $valorInscricao = $this->valorInscricao();
        $metaTotal = $this->metaTotal();

        return [
            'total' => $total,
            'confirmadas' => $confirmadas,
            'aguardando' => $aguardando,
            'canceladas' => $canceladas,
            'valor_arrecadado' => round($confirmadas * $valorInscricao, 2),
            'percentual_meta' => $metaTotal > 0 ? min(100, (int) round(($total / $metaTotal) * 100)) : 0,
            'percentual_confirmadas' => $total > 0 ? (int) round(($confirmadas / $total) * 100) : 0,
            'meta_total' => $metaTotal,
            'valor_inscricao' => $valorInscricao,
            'por_status' => collect(Inscricao::statusOptions())->map(fn (string $label, string $status) => [
                'status' => $status,
                'label' => $label,
                'total' => (int) ($porStatus[$status] ?? 0),
            ])->values()->all(),
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function regionaisCards(): array
    {
        $porRegional = $this->inscricoesPorRegionalQuery()->get()->keyBy('regional_id');
        $valorInscricao = $this->valorInscricao();

        return $this->regionaisFiltro()->map(function (Regional $regional) use ($porRegional, $valorInscricao) {
            $data = $porRegional->get((int) $regional->id);
            $total = (int) ($data->total ?? 0);
            $confirmadas = (int) ($data->confirmadas ?? 0);

            return [
                'regional' => $regional,
                'total' => $total,
                'confirmadas' => $confirmadas,
                'aguardando' => (int) ($data->aguardando ?? 0),
                'canceladas' => (int) ($data->canceladas ?? 0),
                'valor_arrecadado' => round($confirmadas * $valorInscricao, 2),
                'percentual_confirmadas' => $total > 0 ? (int) round(($confirmadas / $total) * 100) : 0,
            ];
        })->values()->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function metasRegionais(): array
    {
        $metaTotal = $this->metaTotal();
        $metaPorRegional = DB::table('inscricao_meta_regionais')
            ->pluck('meta', 'regional_id')
            ->mapWithKeys(fn ($meta, $regionalId) => [(int) $regionalId => (int) $meta]);

        $porRegional = $this->inscricoesPorRegionalQuery()->get()->keyBy('regional_id');
        $regionaisComIgrejas = $this->regionaisFiltro()
            ->filter(fn (Regional $regional) => (int) $regional->igrejas_count > 0)
            ->values();

        $totalIgrejas = (int) $regionaisComIgrejas->sum('igrejas_count');

        if ($totalIgrejas <= 0 || $metaTotal <= 0) {
            return [];
        }

        $base = $regionaisComIgrejas->map(function (Regional $regional) use ($metaTotal, $totalIgrejas, $porRegional) {
            $raw = ($regional->igrejas_count / $totalIgrejas) * $metaTotal;
            $floor = (int) floor($raw);
            $data = $porRegional->get((int) $regional->id);

            return [
                'regional' => $regional,
                'meta' => $floor,
                'remainder' => $raw - $floor,
                'inscricoes_atual' => (int) ($data->total ?? 0),
            ];
        })->values();

        $baseArray = $base->all();
        foreach ($baseArray as $index => $item) {
            $regionalId = (int) $item['regional']->id;
            if ($metaPorRegional->has($regionalId)) {
                $baseArray[$index]['meta'] = (int) $metaPorRegional->get($regionalId);
            }
        }
        $base = collect($baseArray);

        $temMetaManual = $base->contains(fn (array $item) => $metaPorRegional->has((int) $item['regional']->id));
        $faltantes = $metaTotal - $base->sum('meta');
        if (! $temMetaManual && $faltantes > 0) {
            $indices = $base->sortByDesc('remainder')->take($faltantes)->keys()->all();
            $baseArray = $base->all();
            foreach ($indices as $index) {
                if (isset($baseArray[$index])) {
                    $baseArray[$index]['meta']++;
                }
            }
            $base = collect($baseArray);
        }

        return $base
            ->map(function (array $item) {
                $meta = (int) $item['meta'];
                $atual = (int) $item['inscricoes_atual'];
                $item['percentual'] = $meta > 0 ? min(100, (int) round(($atual / $meta) * 100)) : 0;

                return $item;
            })
            ->sortBy(fn (array $item) => $item['regional']->nome)
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function igrejasPorRegional(): array
    {
        $rows = $this->inscricoesPorIgrejaQuery()->get();
        $valorInscricao = $this->valorInscricao();
        $regionais = $this->regionaisFiltro()->keyBy('id');

        $igrejasSemInscricao = Igreja::query()
            ->with('regional')
            ->when(! empty($this->regionalScopeIds), fn ($q) => $q->whereIn('regional_id', $this->regionalScopeIds))
            ->when($this->selectedRegionalId, fn ($q) => $q->where('regional_id', $this->selectedRegionalId))
            ->when($this->selectedIgrejaId, fn ($q) => $q->where('id', $this->selectedIgrejaId))
            ->orderBy('bairro')
            ->get()
            ->keyBy('id');

        $linhas = collect();

        foreach ($rows as $row) {
            $linhas->put((int) $row->igreja_id, [
                'igreja_id' => (int) $row->igreja_id,
                'bairro' => $row->bairro,
                'regional_id' => (int) $row->regional_id,
                'regional_nome' => $regionais[(int) $row->regional_id]->nome ?? '—',
                'dirigente' => $igrejasSemInscricao[(int) $row->igreja_id]->dirigente ?? '—',
                'total' => (int) $row->total,
                'confirmadas' => (int) $row->confirmadas,
                'aguardando' => (int) $row->aguardando,
                'canceladas' => (int) $row->canceladas,
                'valor_arrecadado' => round((int) $row->confirmadas * $valorInscricao, 2),
                'percentual_confirmadas' => (int) $row->total > 0
                    ? (int) round(((int) $row->confirmadas / (int) $row->total) * 100)
                    : 0,
            ]);
        }

        foreach ($igrejasSemInscricao as $igreja) {
            if ($linhas->has((int) $igreja->id)) {
                continue;
            }

            $linhas->put((int) $igreja->id, [
                'igreja_id' => (int) $igreja->id,
                'bairro' => $igreja->bairro,
                'regional_id' => (int) $igreja->regional_id,
                'regional_nome' => $igreja->regional?->nome ?? '—',
                'dirigente' => $igreja->dirigente ?? '—',
                'total' => 0,
                'confirmadas' => 0,
                'aguardando' => 0,
                'canceladas' => 0,
                'valor_arrecadado' => 0.0,
                'percentual_confirmadas' => 0,
            ]);
        }

        $agrupado = $linhas
            ->sortBy([
                ['regional_nome', 'asc'],
                ['bairro', 'asc'],
            ])
            ->groupBy('regional_id')
            ->map(function (Collection $items, int $regionalId) use ($regionais) {
                return [
                    'regional' => $regionais[$regionalId] ?? null,
                    'regional_nome' => $items->first()['regional_nome'] ?? '—',
                    'total' => (int) $items->sum('total'),
                    'confirmadas' => (int) $items->sum('confirmadas'),
                    'aguardando' => (int) $items->sum('aguardando'),
                    'canceladas' => (int) $items->sum('canceladas'),
                    'valor_arrecadado' => round((float) $items->sum('valor_arrecadado'), 2),
                    'igrejas' => $items->values()->all(),
                ];
            })
            ->sortBy(fn (array $grupo) => $grupo['regional_nome'])
            ->values()
            ->all();

        return $agrupado;
    }

    public function inscricoesRecentes(int $perPage = 10): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->with(['igrejaRel.regional'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Inscricao> */
    private function baseQuery()
    {
        $query = Inscricao::query();

        $this->aplicarFiltrosRegionalIgreja($query, 'igrejaRel');

        if ($this->selectedIgrejaId) {
            $query->where('igreja_id', $this->selectedIgrejaId);
        }

        return $query;
    }

    private function inscricoesPorRegionalQuery()
    {
        $query = Inscricao::query()
            ->selectRaw(
                'igrejas.regional_id,
                COUNT(*) as total,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as confirmadas,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as aguardando,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as canceladas',
                [
                    Inscricao::STATUS_CONFIRMADA,
                    Inscricao::STATUS_AGUARDANDO,
                    Inscricao::STATUS_CANCELADA,
                ]
            )
            ->join('igrejas', 'inscricoes.igreja_id', '=', 'igrejas.id')
            ->whereNotNull('igrejas.regional_id');

        $this->aplicarFiltrosRegionalIgrejaJoin($query);

        return $query->groupBy('igrejas.regional_id');
    }

    private function inscricoesPorIgrejaQuery()
    {
        $query = Inscricao::query()
            ->selectRaw(
                'igrejas.id as igreja_id,
                igrejas.bairro,
                igrejas.regional_id,
                COUNT(*) as total,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as confirmadas,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as aguardando,
                SUM(CASE WHEN inscricoes.status = ? THEN 1 ELSE 0 END) as canceladas',
                [
                    Inscricao::STATUS_CONFIRMADA,
                    Inscricao::STATUS_AGUARDANDO,
                    Inscricao::STATUS_CANCELADA,
                ]
            )
            ->join('igrejas', 'inscricoes.igreja_id', '=', 'igrejas.id')
            ->whereNotNull('igrejas.regional_id');

        $this->aplicarFiltrosRegionalIgrejaJoin($query);

        return $query->groupBy('igrejas.id', 'igrejas.bairro', 'igrejas.regional_id');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Inscricao>  $query
     */
    private function aplicarFiltrosRegionalIgreja($query, ?string $relation = null): void
    {
        if (! empty($this->regionalScopeIds)) {
            if ($this->selectedRegionalId) {
                $query->whereHas($relation ?? 'igrejaRel', fn ($q) => $q->where('regional_id', $this->selectedRegionalId));
            } else {
                $query->whereHas($relation ?? 'igrejaRel', fn ($q) => $q->whereIn('regional_id', $this->regionalScopeIds));
            }
        } elseif ($this->selectedRegionalId) {
            $query->whereHas($relation ?? 'igrejaRel', fn ($q) => $q->where('regional_id', $this->selectedRegionalId));
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Inscricao>  $query
     */
    private function aplicarFiltrosRegionalIgrejaJoin($query): void
    {
        if (! empty($this->regionalScopeIds)) {
            $query->whereIn('igrejas.regional_id', $this->selectedRegionalId ? [$this->selectedRegionalId] : $this->regionalScopeIds);
        } elseif ($this->selectedRegionalId) {
            $query->where('igrejas.regional_id', $this->selectedRegionalId);
        }

        if ($this->selectedIgrejaId) {
            $query->where('inscricoes.igreja_id', $this->selectedIgrejaId);
        }
    }
}
