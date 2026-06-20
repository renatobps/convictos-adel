<?php

namespace App\Models;

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
        'pago' => 'Pago',
        'enviado' => 'Enviado',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->reference)) {
                $order->reference = 'CV-' . strtoupper(Str::random(8));
            }
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
