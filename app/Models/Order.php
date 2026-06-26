<?php

namespace App\Models;

use App\Services\OrderNotifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total',
        'status',
        'payment_method',
        'payment_status',
        'payment_id',
        'notes',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public const STATUSES = [
        'pendente' => 'Pendente',
        'em_separacao' => 'Pedido em separação',
        'pronto_retirada' => 'Pronto para retirada',
        'retirado' => 'Retirado',
        'cancelado' => 'Cancelado',
    ];

    public static function statusPosPagamento(): string
    {
        return 'em_separacao';
    }

    public function pagamentoConfirmado(): bool
    {
        return in_array($this->status, ['em_separacao', 'pronto_retirada', 'retirado'], true);
    }

    public function mensagemStatusCliente(): string
    {
        return match ($this->status) {
            'pendente' => 'Seu pedido está aguardando a confirmação do pagamento.',
            'em_separacao' => 'Pagamento confirmado! Seu pedido está em separação e em breve ficará pronto para retirada.',
            'pronto_retirada' => 'Seu pedido está pronto para retirada! Compareça à '.(\App\Support\LojaRetiradaConfig::local()).' nos horários disponíveis.',
            'retirado' => 'Pedido retirado com sucesso. Obrigado por vestir a convicção!',
            'cancelado' => 'Seu pedido foi cancelado. Em caso de dúvidas, fale conosco.',
            default => 'O status do seu pedido foi atualizado para '.$this->status_label.'.',
        };
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->reference)) {
                $order->reference = 'CV-' . strtoupper(Str::random(8));
            }
        });

        static::updated(function (Order $order): void {
            if (! $order->wasChanged('status')) {
                return;
            }

            $statusAnterior = (string) $order->getOriginal('status');
            if ($statusAnterior === '') {
                return;
            }

            app(OrderNotifier::class)->notifyStatusChanged($order, $statusAnterior);
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
