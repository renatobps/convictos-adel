<?php

namespace App\Models;

use App\Services\QrCodeService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Inscricao extends Model
{
    public const STATUS_AGUARDANDO = 'aguardando';

    public const STATUS_CONFIRMADA = 'confirmada';

    public const STATUS_CANCELADA = 'cancelada';

    public const TAMANHO_P = 'P';

    public const TAMANHO_M = 'M';

    public const TAMANHO_G = 'G';

    public const TAMANHO_GG = 'GG';

    public const TAMANHO_XG = 'XG';

    protected $table = 'inscricoes';

    protected $fillable = [
        'codigo',
        'nome',
        'email',
        'whatsapp',
        'idade',
        'tamanho_camiseta',
        'camiseta_retirada',
        'camiseta_retirada_em',
        'camiseta_retirada_por',
        'igreja',
        'igreja_id',
        'lider_jovens',
        'cidade',
        'status',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'lider_jovens' => 'boolean',
            'camiseta_retirada' => 'boolean',
            'camiseta_retirada_em' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Inscricao $inscricao): void {
            if (blank($inscricao->codigo)) {
                $inscricao->codigo = static::gerarCodigoUnico();
            }
        });
    }

    public static function gerarCodigoUnico(): string
    {
        do {
            $codigo = 'CV27-'.strtoupper(Str::random(6));
        } while (static::query()->where('codigo', $codigo)->exists());

        return $codigo;
    }

    public function urlIngresso(): string
    {
        return route('ingresso.show', ['inscricao' => $this->codigo]);
    }

    /**
     * Conteúdo codificado no QR Code: link público do ingresso digital.
     */
    public function qrConteudo(): string
    {
        return $this->urlIngresso();
    }

    public function qrDataUri(int $size = 300): string
    {
        return app(QrCodeService::class)->dataUri($this->qrConteudo(), $size);
    }

    public function igrejaRel(): BelongsTo
    {
        return $this->belongsTo(Igreja::class, 'igreja_id');
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_AGUARDANDO => 'Aguardando',
            self::STATUS_CONFIRMADA => 'Confirmada',
            self::STATUS_CANCELADA => 'Cancelada',
        ];
    }

    public static function tamanhoCamisetaOptions(): array
    {
        return [
            self::TAMANHO_P => self::TAMANHO_P,
            self::TAMANHO_M => self::TAMANHO_M,
            self::TAMANHO_G => self::TAMANHO_G,
            self::TAMANHO_GG => self::TAMANHO_GG,
            self::TAMANHO_XG => self::TAMANHO_XG,
        ];
    }
}
