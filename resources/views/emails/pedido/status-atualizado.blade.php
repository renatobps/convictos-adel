@php
    $statusAnteriorLabel = \App\Models\Order::STATUSES[$statusAnterior] ?? $statusAnterior;
@endphp

<x-mail::message>
# Atualização do seu pedido

Olá, **{{ $order->customer_name }}**!

O pedido **{{ $order->reference }}** teve o status alterado:

- **De:** {{ $statusAnteriorLabel }}
- **Para:** {{ $order->status_label }}

{{ $order->mensagemStatusCliente() }}

@if($order->status === 'pronto_retirada' || $order->status === 'em_separacao')
<x-mail::panel>
**Retirada em {{ \App\Support\LojaRetiradaConfig::local() }}**

{{ \App\Support\LojaRetiradaConfig::instrucoes() }}

@foreach(\App\Support\LojaRetiradaConfig::linhasHorarios() as $horario)
- {{ $horario }}
@endforeach
</x-mail::panel>
@endif

<x-mail::table>
| Produto | Tam. | Qtd | Valor |
| :------ | :--: | :-: | ----: |
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->size ?: '—' }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }} |
@endforeach
</x-mail::table>

**Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}**

Abraço,<br>
**Equipe Convictos**
</x-mail::message>
