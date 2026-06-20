@extends('layouts.site')

@section('title', 'Carrinho — Loja Convictos')

@section('content')
<section class="cart-page">
  <div class="cart-inner">
    <span class="label">Seu Pedido</span>
    <h1 class="title" style="margin-bottom:40px;">CARRINHO</h1>

    @if(count($items) === 0)
      <div class="cart-empty">
        <p>Seu carrinho está vazio.</p>
        <a href="{{ route('store.index') }}" class="btn-primary">Ir para a loja</a>
      </div>
    @else
      <div class="cart-list">
        @foreach($items as $rowId => $item)
          <div class="cart-item">
            <div class="cart-item-media"><img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"></div>
            <div class="cart-item-info">
              <div class="cart-item-name">{{ $item['name'] }}</div>
              @if($item['size'])<div class="cart-item-size">Tamanho: {{ $item['size'] }}</div>@endif
              <div class="cart-item-price">R$ {{ number_format($item['price'], 2, ',', '.') }}</div>
            </div>
            <form method="POST" action="{{ route('cart.update', $rowId) }}" class="cart-item-qty">
              @csrf
              @method('PATCH')
              <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="0" max="50">
              <button type="submit" class="cart-qty-btn">Atualizar</button>
            </form>
            <div class="cart-item-subtotal">R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</div>
            <form method="POST" action="{{ route('cart.remove', $rowId) }}">
              @csrf
              @method('DELETE')
              <button type="submit" class="cart-remove" title="Remover">✕</button>
            </form>
          </div>
        @endforeach
      </div>

      <div class="cart-footer">
        <a href="{{ route('store.index') }}" class="btn-outline">Continuar comprando</a>
        <div class="cart-total-box">
          <span class="cart-total-label">Total</span>
          <span class="cart-total-value">R$ {{ number_format($total, 2, ',', '.') }}</span>
        </div>
      </div>
      <div style="text-align:right;margin-top:24px;">
        <a href="{{ route('checkout.index') }}" class="btn-primary">Finalizar compra</a>
      </div>
    @endif
  </div>
</section>
@endsection
