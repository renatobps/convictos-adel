<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class NotificacaoGrupo extends Model
{
    public const TIPO_IGREJA = 'igreja';

    public const TIPO_REGIONAL = 'regional';

    public const TIPO_INSCRITOS = 'inscritos';

    protected $fillable = [
        'nome',
        'tipo',
        'igreja_id',
        'regional_id',
        'status_inscricao',
        'sistema',
    ];

    protected function casts(): array
    {
        return [
            'sistema' => 'boolean',
        ];
    }

    public function igreja(): BelongsTo
    {
        return $this->belongsTo(Igreja::class);
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }

    public function enquetes(): HasMany
    {
        return $this->hasMany(Enquete::class);
    }

    public static function tipoOptions(): array
    {
        return [
            self::TIPO_IGREJA => 'Por igreja',
            self::TIPO_REGIONAL => 'Por regional',
            self::TIPO_INSCRITOS => 'Inscritos',
        ];
    }

    public function tipoLabel(): string
    {
        return self::tipoOptions()[$this->tipo] ?? $this->tipo;
    }

    public function descricaoDestino(): string
    {
        return match ($this->tipo) {
            self::TIPO_IGREJA => $this->igreja?->bairro ?? '—',
            self::TIPO_REGIONAL => $this->regional?->nome ?? '—',
            self::TIPO_INSCRITOS => $this->status_inscricao
                ? (Inscricao::statusOptions()[$this->status_inscricao] ?? $this->status_inscricao)
                : 'Todos os inscritos',
            default => '—',
        };
    }

    /** @return Builder<Inscricao> */
    public function inscricoesQuery(): Builder
    {
        $query = Inscricao::query()
            ->whereNotNull('whatsapp')
            ->where('whatsapp', '!=', '');

        return match ($this->tipo) {
            self::TIPO_IGREJA => $query->where('igreja_id', $this->igreja_id),
            self::TIPO_REGIONAL => $query->whereHas(
                'igrejaRel',
                fn (Builder $q) => $q->where('regional_id', $this->regional_id)
            ),
            self::TIPO_INSCRITOS => $this->aplicarFiltroStatus($query),
            default => $query->whereRaw('1 = 0'),
        };
    }

    public function contarDestinatarios(): int
    {
        return $this->inscricoesQuery()->count();
    }

    /** @return Builder<Inscricao> */
    private function aplicarFiltroStatus(Builder $query): Builder
    {
        if ($this->status_inscricao === null || $this->status_inscricao === '') {
            return $query;
        }

        if ($this->status_inscricao === Inscricao::STATUS_AGUARDANDO) {
            return $query->where(fn (Builder $q) => $q
                ->where('status', Inscricao::STATUS_AGUARDANDO)
                ->orWhereNull('status'));
        }

        return $query->where('status', $this->status_inscricao);
    }
}
