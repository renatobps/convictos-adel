@extends('layouts.site')

@section('title', 'Finalizar Compra — Loja Convictos')

@section('content')
<section class="checkout-page">
  <div class="checkout-inner">
    <span class="label">Quase lá</span>
    <h1 class="title" style="margin-bottom:40px;">FINALIZAR</h1>

    @if($errors->any())
      <div class="flash flash-error" style="position:static;margin:0 0 24px;">
        @foreach($errors->all() as $error){{ $error }}<br>@endforeach
      </div>
    @endif

    <div class="checkout-grid">
      <form method="POST" action="{{ route('checkout.store') }}" class="checkout-form">
        @csrf
        <div class="field">
          <label>Nome completo *</label>
          <input type="text" name="customer_name" class="form-input" value="{{ old('customer_name') }}" required>
        </div>
        <div class="field">
          <label>E-mail *</label>
          <input type="email" name="customer_email" class="form-input" value="{{ old('customer_email') }}" required>
        </div>
        <div class="field">
          <label>WhatsApp / Telefone</label>
          <input type="tel" name="customer_phone" class="form-input" placeholder="(99) 99999-9999" inputmode="numeric" maxlength="15" data-phone value="{{ old('customer_phone') }}">
        </div>
        <div class="field">
          <label>Observações (tamanho, ponto de entrega, etc.)</label>
          <textarea name="notes" class="form-input" rows="3">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;">
          @if($mercadoPagoEnabled) Pagar com MercadoPago @else Confirmar pedido @endif
        </button>

        @unless($mercadoPagoEnabled)
          <p class="checkout-note">O pagamento online ainda não está ativo. Seu pedido será registrado e nossa equipe entrará em contato para combinar o pagamento.</p>
        @endunless
      </form>

      <aside class="checkout-summary">
        <h3>Resumo do pedido</h3>
        @foreach($items as $item)
          <div class="summary-row">
            <span>{{ $item['quantity'] }}× {{ $item['name'] }}@if($item['size']) ({{ $item['size'] }})@endif</span>
            <span>R$ {{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }}</span>
          </div>
        @endforeach
        <div class="summary-total">
          <span>Total</span>
          <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
