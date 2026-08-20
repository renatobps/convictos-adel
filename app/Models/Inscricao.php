<?php

namespace App\Models;

use App\Services\QrCodeService;
use App\Support\TelefoneBr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Inscricao extends Model
{
    public const STATUS_AGUARDANDO = 'aguardando';

    /** Alias semântico: inscrição aguardando pagamento PIX. */
    public const STATUS_PENDENTE = self::STATUS_AGUARDANDO;

    public const STATUS_CONFIRMADA = 'confirmada';

    /** Alias semântico: pagamento PIX recebido / confirmado pelo coordenador. */
    public const STATUS_PAGO = self::STATUS_CONFIRMADA;

    public const STATUS_CANCELADA = 'cancelada';

    public const TAMANHO_P = 'P';

    public const TAMANHO_M = 'M';

    public const TAMANHO_G = 'G';

    public const TAMANHO_GG = 'GG';

    public const TAMANHO_XG = 'XG';

    public const TIPO_COM_CAMISETA = 'com_camiseta';

    public const TIPO_SEM_CAMISETA = 'sem_camiseta';

    protected $table = 'inscricoes';

    protected $fillable = [
        'codigo',
        'nome',
        'email',
        'whatsapp',
        'idade',
        'tipo_ingresso',
        'valor',
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
            'valor' => 'decimal:2',
        ];
    }

    public function comCamiseta(): bool
    {
        return ($this->tipo_ingresso ?? self::TIPO_COM_CAMISETA) === self::TIPO_COM_CAMISETA;
    }

    public function estaPendente(): bool
    {
        return ($this->status ?? self::STATUS_PENDENTE) === self::STATUS_PENDENTE;
    }

    public function estaPaga(): bool
    {
        return $this->status === self::STATUS_PAGO;
    }

    public function tipoIngressoLabel(): string
    {
        return self::tipoIngressoOptions()[$this->tipo_ingresso ?? self::TIPO_COM_CAMISETA]
            ?? (string) $this->tipo_ingresso;
    }

    /**
     * Dispara WhatsApp/e-mail quando o status passa a Pago (pagamento PIX confirmado).
     */
    public function notificarSePagamentoConfirmado(?string $statusAnterior): void
    {
        if ($statusAnterior === self::STATUS_PAGO || $this->status !== self::STATUS_PAGO) {
            return;
        }

        try {
            app(\App\Services\WhatsAppService::class)->enviarConfirmacao($this);
        } catch (\Throwable) {
            // Falha de notificação não deve impedir a confirmação do pagamento.
        }

        \App\Support\EmailConfig::enviarParaInscricao($this, \App\Support\EmailConfig::TIPO_CONFIRMADA);
    }

    protected static function booted(): void
    {
        static::creating(function (Inscricao $inscricao): void {
            if (blank($inscricao->codigo)) {
                $inscricao->codigo = static::gerarCodigoUnico();
            }
        });

        static::saving(function (Inscricao $inscricao): void {
            if (($inscricao->tipo_ingresso ?? self::TIPO_COM_CAMISETA) === self::TIPO_SEM_CAMISETA) {
                $inscricao->tamanho_camiseta = null;
            }

            if (blank($inscricao->tipo_ingresso)) {
                $inscricao->tipo_ingresso = self::TIPO_COM_CAMISETA;
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

    /**
     * Verifica se já existe inscrição ativa (não cancelada) com o mesmo celular.
     */
    public static function jaExisteWhatsapp(string $whatsapp, ?int $excetoId = null): bool
    {
        $chave = TelefoneBr::chaveComparacao($whatsapp);

        if ($chave === null) {
            return false;
        }

        return static::query()
            ->where('status', '!=', self::STATUS_CANCELADA)
            ->when($excetoId, fn ($q) => $q->where('id', '!=', $excetoId))
            ->get(['id', 'whatsapp'])
            ->contains(fn (self $inscricao): bool => TelefoneBr::chaveComparacao($inscricao->whatsapp) === $chave);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDENTE => 'Pendente',
            self::STATUS_PAGO => 'Pago',
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

    public static function tipoIngressoOptions(): array
    {
        return [
            self::TIPO_COM_CAMISETA => 'Com camiseta',
            self::TIPO_SEM_CAMISETA => 'Sem camiseta',
        ];
    }
}
