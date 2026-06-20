<x-mail::message>
# Novo pedido na loja

Pedido **{{ $order->reference }}** registrado.

- **Cliente:** {{ $order->customer_name }}
- **E-mail:** {{ $order->customer_email }}
- **Telefone:** {{ $order->customer_phone ?: '—' }}
- **Pagamento:** {{ $order->payment_method ?: '—' }} ({{ $order->status_label }})
- **Data:** {{ $order->created_at->format('d/m/Y H:i') }}

<x-mail::table>
| Produto | Tam. | Qtd | Valor |
| :------ | :--: | :-: | ----: |
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->size ?: '—' }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }} |
@endforeach
</x-mail::table>

**Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}**

@if($order->notes)
**Observações:** {{ $order->notes }}
@endif

<x-mail::button :url="url('/admin/orders/' . $order->id . '/edit')">
Ver no painel
</x-mail::button>
</x-mail::message>
