<x-mail::message>
# Pedido confirmado! 🛒

Olá, **{{ $order->customer_name }}**!

Recebemos o seu pedido **{{ $order->reference }}**. Obrigado por vestir a convicção!

<x-mail::table>
| Produto | Tam. | Qtd | Valor |
| :------ | :--: | :-: | ----: |
@foreach($order->items as $item)
| {{ $item->product_name }} | {{ $item->size ?: '—' }} | {{ $item->quantity }} | R$ {{ number_format((float) $item->subtotal, 2, ',', '.') }} |
@endforeach
</x-mail::table>

**Total: R$ {{ number_format((float) $order->total, 2, ',', '.') }}**

@if($order->payment_method === 'manual')
Seu pedido foi registrado. Nossa equipe entrará em contato pelo e-mail ou WhatsApp informado para combinar o pagamento. A retirada é na {{ \App\Support\LojaRetiradaConfig::local() }}.
@elseif($order->pagamentoConfirmado())
Pagamento **confirmado**! Seu pedido está em separação e em breve ficará pronto para retirada.
@else
Assim que o pagamento for confirmado, atualizaremos o status do seu pedido.
@endif

> "Para que todos sejam um." — João 17:21

Abraço,<br>
**Equipe Convictos**
</x-mail::message>
